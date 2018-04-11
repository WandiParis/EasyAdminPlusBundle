<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Property;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Id;
use Wandi\EasyAdminPlusBundle\Generator\Helper\PropertyHelper;
use Wandi\EasyAdminPlusBundle\Generator\Type\TypeGuesser;

class PropertyConfig
{
    /**
     * @var array
     */
    private static $defaultPropertyConfig = [
        'name' => '',
        'annotationClasses' => [],
        'typeConfig' => null,
    ];

    /**
     * @param \ReflectionProperty $reflectProperty
     *
     * @return array
     */
    public static function setPropertyConfig(\ReflectionProperty $reflectProperty): array
    {
        $annotationReader = new AnnotationReader();
        $propertyAnnotations = $annotationReader->getPropertyAnnotations($reflectProperty);
        $propertyConfig = self::$defaultPropertyConfig;

        $typeGuessed = TypeGuesser::getGuessType($reflectProperty->name, $reflectProperty->class);

        $propertyConfig['name'] = $reflectProperty->getName();
        $propertyConfig['typeConfig'] = TypeGuesser::$generatorTypesConfiguration[$typeGuessed];
        $propertyConfig['typeConfig'] = array_replace(TypeGuesser::$defaultConfigType, TypeGuesser::$generatorTypesConfiguration[$typeGuessed]);
        $propertyConfig['annotationClasses'] = $propertyAnnotations;

        if (PropertyHelper::hasClass($propertyConfig['annotationClasses'], Id::class)) {
            $propertyConfig['typeConfig']['methodsNoAllowed'] = array_merge($propertyConfig['typeConfig']['methodsNoAllowed'], ['new', 'edit']);
        }

        return $propertyConfig;
    }
}
