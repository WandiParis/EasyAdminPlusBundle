<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Helper;

use Doctrine\ORM\Mapping\OneToMany;
use Symfony\Component\Translation\Translator;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;
use Doctrine\ORM\Mapping\Column;
use Wandi\EasyAdminPlusBundle\Generator\GeneratorTool;
use Wandi\EasyAdminPlusBundle\Generator\Exception\EAException;
use Wandi\EasyAdminPlusBundle\Generator\Model\Field;
use Wandi\EasyAdminPlusBundle\Generator\Model\Method;
use Wandi\EasyAdminPlusBundle\Generator\Type\EasyAdminType;
use Wandi\EasyAdminPlusBundle\Generator\Type\TypeGuesser;

class PropertyTypeHelper extends AbstractPropertyHelper
{
    const FORMAT_DATETIMETZ = 'd/m/Y à H\hi e';

    private static $vichTypes = [
        EasyAdminType::VICH_FILE,
        EasyAdminType::VICH_IMAGE,
    ];

    private static $typeHelpers = [
        EasyAdminType::IMAGE => [
            'function' => 'handleImage',
            'methods' => [
            ],
        ],
        EasyAdminType::DECIMAL => [
            'function' => 'handleDecimal',
            'methods' => [
                'list',
                'show',
            ],
        ],
        EasyAdminType::AUTOCOMPLETE => [
            'function' => 'handleAutoComplete',
        ],
        EasyAdminType::DATETIMETZ => [
            'function' => 'handleDatetimetz',
        ],
    ];

    public static function setTypeHelpers(array $typeHelpers): void
    {
        self::$typeHelpers = $typeHelpers;
    }

    public static function getTypeHelpers(): array
    {
        return self::$typeHelpers;
    }

    /**
     * @param array  $propertyConfig
     * @param Field  $field
     * @param Method $method
     *
     * @throws EAException
     */
    public static function handleImage(array $propertyConfig, Field $field, Method $method): void
    {
        /** @var UploadableField $uploadableField */
        $uploadableField = PropertyHelper::getClassFromArray($propertyConfig['annotationClasses'], UploadableField::class);

        if (!isset(GeneratorTool::getParameterBag()['vich_uploader.mappings'])) {
            throw new EaException('No vich mappings detected');
        }

        if (!isset((GeneratorTool::getParameterBag()['vich_uploader.mappings'])[$uploadableField->getMapping()])) {
            throw new EaException('No vich mappings detected for ' . $uploadableField->getMapping());
        }

        $mapping = (GeneratorTool::getParameterBag()['vich_uploader.mappings'])[$uploadableField->getMapping()];

        if (!isset($mapping['uri_prefix'])) {
            throw new EaException('The uri_prefix index doest not exist ');
        }
        $param = array_search($mapping['uri_prefix'], GeneratorTool::getParameterBag(), true);

        if (!$param) {
            throw new EaException(sprintf('Can not find the parameter relative to the specified value (%s)', $mapping['uri_prefix']));
        }

        $field->setBasePath('%'.$param.'%');
    }

    /**
     * @param array  $propertyConfig
     * @param Field  $field
     * @param Method $method
     */
    public static function handleDecimal(array $propertyConfig, Field $field, Method $method): void
    {
        /** @var Column $column */
        $column = PropertyHelper::getClassFromArray($propertyConfig['annotationClasses'], Column::class);
        if (null === $column) {
            return;
        }

        /** @var Translator $translator */
        $translator = GeneratorTool::getTranslation();

        if (in_array($method->getName(), ['list', 'show'])) {
            $field->setFormat('%'.($column->precision - $column->scale).'.'.$column->scale.'f');
        } elseif (in_array($method->getName(), ['new', 'edit'])) {
            $typeOptions = $field->getTypeOptions();
            if (!isset($typeOptions['attr']['pattern'])) {
                $regex = '^(?=(\D*[0-9]){0,'.$column->precision.'}$)-?[0-9]*(\.[0-9]{0,'.$column->scale.'})?$';
                $typeOptions['attr']['pattern'] = $regex;
                $typeOptions['attr']['title'] = $translator->trans('generator.decimal.title', ['%value%' => $column->scale]);
                $field->setTypeOptions($typeOptions);
            }
        }
    }

    /**
     * @param array  $propertyConfig
     * @param Field  $field
     * @param Method $method
     */
    public static function handleAutoComplete(array $propertyConfig, Field $field, Method $method): void
    {
        if ('list' == $method->getName() && PropertyHelper::getClassFromArray($propertyConfig['annotationClasses'], OneToMany::class)) {
            $field->setName(null);

            return;
        }

        if (PropertyHelper::getClassFromArray($propertyConfig['annotationClasses'], OneToMany::class)
            && 'show' != $method->getName()) {
            $typeOptions = $field->getTypeOptions();
            $typeOptions['by_reference'] = false;
            $field->setTypeOptions($typeOptions);
        }
    }

    /**
     * @param array  $propertyConfig
     * @param Field  $field
     * @param Method $method
     */
    public static function handleDatetimetz(array $propertyConfig, Field $field, Method $method): void
    {
        if (in_array($method->getName(), ['list', 'show'])) {
            $field->setFormat(self::FORMAT_DATETIMETZ);
        }
    }

    /**
     * @param array $properties
     *
     * @return array
     *
     * @throws EAException
     */
    public static function setVichPropertiesConfig(array $properties): array
    {
        $vichProperties = array_filter($properties, function ($property) {
            return in_array($property['typeConfig']['easyAdminType'], self::$vichTypes);
        });

        foreach ($vichProperties as $vichProperty) {
            $uploadableField = PropertyHelper::getClassFromArray($vichProperty['annotationClasses'], UploadableField::class);

            /** @var UploadableField $uploadableField */
            if (!$uploadableField) {
                continue;
            }

            $propertyTargeted = array_values(array_filter($properties, function ($property) use ($uploadableField) {
                return $property['name'] == $uploadableField->getFileNameProperty();
            }));

            $propertyTargeted = $propertyTargeted[0] ?? null;

            if (!$propertyTargeted) {
                throw new EAException('Bad fileNameProperty (Vich property)');
            }
            $config = array_values(array_filter(TypeGuesser::$generatorTypesConfiguration, function ($type) {
                return EasyAdminType::IMAGE == $type['easyAdminType'];
            }))[0];

            foreach ($properties as &$property) {
                if ($property === $propertyTargeted) {
                    $property['typeConfig'] = array_replace(TypeGuesser::$defaultConfigType, $config);
                    $property['annotationClasses'][] = $uploadableField;
                }
            }
        }

        return $properties;
    }
}
