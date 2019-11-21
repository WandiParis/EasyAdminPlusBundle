<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Helper;

use Doctrine\ORM\Mapping\OneToMany;
use Symfony\Component\Translation\Translator;
use Doctrine\ORM\Mapping\Column;
use Wandi\EasyAdminPlusBundle\Generator\GeneratorTool;
use Wandi\EasyAdminPlusBundle\Generator\Model\Field;
use Wandi\EasyAdminPlusBundle\Generator\Model\Method;
use Wandi\EasyAdminPlusBundle\Generator\Type\EasyAdminType;

class PropertyTypeHelper extends AbstractPropertyHelper
{
    const FORMAT_DATETIMETZ = 'd/m/Y à H\hi e';

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

    public static function handleImage(array $propertyConfig, Field $field, Method $method): void
    {
        $field
            ->setTemplate('@WandiEasyAdminPlus/templates/vich_uploader_image.html.twig')
            ->setPropertyFile($propertyConfig['typeConfig']['propertyFile'])
        ;
    }

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

    public static function handleDatetimetz(array $propertyConfig, Field $field, Method $method): void
    {
        if (in_array($method->getName(), ['list', 'show'])) {
            $field->setFormat(self::FORMAT_DATETIMETZ);
        }
    }
}
