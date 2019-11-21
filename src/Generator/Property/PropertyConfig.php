<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Property;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Id;
use Wandi\EasyAdminPlusBundle\Generator\Helper\PropertyHelper;
use Wandi\EasyAdminPlusBundle\Generator\Type\EasyAdminType;
use Wandi\EasyAdminPlusBundle\Generator\Type\TypeGuesser;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;

class PropertyConfig
{
    private static $vichTypes = [
        EasyAdminType::VICH_FILE,
        EasyAdminType::VICH_IMAGE,
    ];

    public static function setPropertyConfig(\ReflectionProperty $reflectProperty): array
    {
        $typeGuessed = TypeGuesser::getGuessType($reflectProperty->name, $reflectProperty->class);
        $propertyConfig = [
            'name' => $reflectProperty->getName(),
            'annotationClasses' => (new AnnotationReader())->getPropertyAnnotations($reflectProperty),
            'typeConfig' => array_replace(TypeGuesser::$defaultConfigType, TypeGuesser::$generatorTypesConfiguration[$typeGuessed]),
        ];

        if (PropertyHelper::hasClass($propertyConfig['annotationClasses'], Id::class)) {
            $propertyConfig['typeConfig']['methodsNoAllowed'] = array_merge($propertyConfig['typeConfig']['methodsNoAllowed'], ['new', 'edit']);
        }

        return $propertyConfig;
    }

    public static function setVichPropertiesConfig(array &$properties): void
    {
        $imageConfig = array_replace(
            TypeGuesser::$defaultConfigType,
            array_values(array_filter(TypeGuesser::$generatorTypesConfiguration, function ($type) {
                return EasyAdminType::IMAGE == $type['easyAdminType'];
            }))[0]
        );
        $vichProperties = array_filter($properties, function ($property) {
            return in_array($property['typeConfig']['easyAdminType'], self::$vichTypes);
        });

        foreach ($vichProperties as $vichProperty) {
            $uploadableField = PropertyHelper::getClassFromArray($vichProperty['annotationClasses'], UploadableField::class);

            /** @var UploadableField $uploadableField */
            if (!$uploadableField) {
                continue;
            }

            $nameTargeted = explode('.', $uploadableField->getFileNameProperty())[0];
            $propertyTargeted = array_values(array_filter($properties, function ($property) use ($nameTargeted) {
                return $property['name'] == $nameTargeted;
            }));

            if (null === $propertyTargeted = $propertyTargeted[0] ?? null) {
                continue;
            }
            
            foreach ($properties as &$property) {
                if ($property === $propertyTargeted) {
                    $property['typeConfig'] = array_merge($imageConfig, ['propertyFile' => $vichProperty['name']]);
                    $property['annotationClasses'][] = $uploadableField;
                }
            }
        }
    }

    /**
     * Set DateTime type
     * Disable methods 'new', 'edit' method for createdAt/updatedAt fields.
     */
    public static function setTimestampablePropertiesConfig(array &$properties): void
    {
        $targeteds = ['createdAt', 'updatedAt'];
        $dateTimeConfig = array_replace(
            TypeGuesser::$defaultConfigType,
            array_values(array_filter(TypeGuesser::$generatorTypesConfiguration, function ($type) {
                return EasyAdminType::DATETIME == $type['easyAdminType'];
            }))[0]
        );
        $timestampableProperties = array_filter($properties, function ($property) use ($targeteds) {
            return in_array($property['name'], $targeteds);
        });

        foreach ($timestampableProperties as &$timestampableProperty) {
            $timestampableProperty['typeConfig'] = array_merge(
                $dateTimeConfig,
                ['methodsNoAllowed' => ['new', 'edit']]
            );
        }

        $properties = array_replace($properties, $timestampableProperties);
    }

    /**
     * Remove virtual property local
     */
    public static function setTranslatableEntityPropertiesConfig(array &$properties): void
    {
        $properties = array_filter($properties, function ($property) {
            return 'locale' !== $property['name'];
        });
    }
}
