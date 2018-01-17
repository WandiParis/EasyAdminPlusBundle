<?php

namespace Wandi\EasyAdminPlusBundle\Generator;

use Doctrine\ORM\Mapping\OneToMany;
use Symfony\Component\Translation\Translator;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;
use Doctrine\ORM\Mapping\Column;
use Wandi\EasyAdminPlusBundle\Generator\Exception\EAException;



class PropertyTypeHelperFunctions
{
    const FORMAT_DATETIMETZ = 'd/m/Y à H\hi e';

    /**
     * Set l'option base_path
     * @param array $propertyConfig
     * @param Field $field
     * @param Method $method
     * @throws EAException
     */
    public static function handleImage(array $propertyConfig, Field $field, Method $method): void
    {
        /** @var UploadableField $uploadableField */
        $uploadableField = ConfigurationTypes::getClassFromArray( $propertyConfig['annotationClasses'], UploadableField::class);

        if (!isset(EATool::getParameterBag()['vich_uploader.mappings']))
        {
            throw new EaException('No vich mappings detected');
        }

        $mapping = (EATool::getParameterBag()['vich_uploader.mappings'])[$uploadableField->getMapping()];

        if (!isset($mapping['uri_prefix']))
        {
            throw new EaException('The uri_prefix index doest not exist ');
        }
        $param = array_search($mapping['uri_prefix'], EATool::getParameterBag(), true);

        if (!$param)
        {
            throw new EaException(sprintf('Can not find the parameter relative to the specified value (%s)', $mapping['uri_prefix']));
        }

        $field->setBasePath("%" . $param . "%");
    }

    /**
     * Format pour les méthodes list/show
     * Attribut pattern pour les méthodes add/edit
     * @param array $propertyConfig
     * @param Field $field
     * @param Method $method
     */
    public static function handleDecimal(array $propertyConfig, Field $field, Method $method): void
    {
        /** @var Column $column */
        $column = ConfigurationTypes::getClassFromArray($propertyConfig['annotationClasses'], Column::class);
        if ($column === null)
            return ;

        /** @var  Translator $translator */
        $translator = EATool::getTranslation();

        if (in_array($method->getName(), ['list', 'show']))
        {
            $field->setFormat('%' . ($column->precision - $column->scale) . '.' . $column->scale . 'f');
        }
        else if (in_array($method->getName(), ['new', 'edit']))
        {
            $typeOptions = $field->getTypeOptions();
            //Si un Pattern n'est pas déjà précisé (classHelper), sinon on n'écrase pas
            if (!isset($typeOptions['attr']['pattern']))
            {
                $regex = '^(?=(\D*[0-9]){0,' . $column->precision . '}$)-?[0-9]*(\.[0-9]{0,' . $column->scale . '})?$';
                $typeOptions['attr']['pattern'] = $regex;
                $typeOptions['attr']['title'] = $translator->trans('ea_tool.decimal.title', ['%value%' => $column->scale]);
                $field->setTypeOptions($typeOptions);
            }
        }
    }

    /**
     * @param array $propertyConfig
     * @param Field $field
     * @param Method $method
     */
    public static function handleAutoComplete(array $propertyConfig, Field $field, Method $method): void
    {
        //Si OneToMany et method list on set le name à null pour pas l'afficher
        if ($method->getName() == 'list' && ConfigurationTypes::getClassFromArray($propertyConfig['annotationClasses'], OneToMany::class))
        {
            $field->setName(null);
            return ;
        }

        //Si OneToMany, on rajoute reference by false
        if (ConfigurationTypes::getClassFromArray($propertyConfig['annotationClasses'], OneToMany::class)
            && $method->getName() != 'show')
        {
            $typeOptions = $field->getTypeOptions();
            $typeOptions['by_reference'] = false;
            $field->setTypeOptions($typeOptions);
        }
    }

    /**
     * @param array $propertyConfig
     * @param Field $field
     * @param Method $method
     */
    public static function handleDatetimetz(array $propertyConfig, Field $field, Method $method): void
    {
        if (in_array($method->getName(), ['list', 'show']))
        {
            $field->setFormat(self::FORMAT_DATETIMETZ);
        }
    }
}