<?php

namespace Wandi\EasyAdminPlusBundle\Generator;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Id;

class PropertyConfig
{
    /**
     * Configuration de base d'une propriété
     */
    private static $defaultPropertyConfig = [
        //nom de la propriété
        'name' => '',
        //Classes annotations
        'annotationClasses' => [],
        //configuration du type choisi
        'typeConfig' => null
    ];

    /**
     * Set la configuration d'une propriété (ces classes, son type, etc)
     * @param \ReflectionProperty $reflectProperty
     * @return array
     */
    public static function setPropertyConfig(\ReflectionProperty $reflectProperty): array
    {
        $annotationReader = new AnnotationReader();
        $propertyAnnotations = $annotationReader->getPropertyAnnotations($reflectProperty);
        $propertyConfig = self::$defaultPropertyConfig;

        foreach ($propertyAnnotations as $annotation)
        {
            $propertyConfig['annotationClasses'][] = $annotation;
        }

        $propertyConfig['name'] = $reflectProperty->getName();
        $propertyConfig = array_replace($propertyConfig, self::buildPropertyTypes($propertyConfig));

        //Si clef primaire, on retire les methods new et edit
        if (ConfigurationTypes::hasClass($propertyConfig['annotationClasses'], Id::class))
            $propertyConfig['typeConfig']['methodsNoAllowed'] = array_merge($propertyConfig['typeConfig']['methodsNoAllowed'], ['new', 'edit']);

        return $propertyConfig;
    }

    /**
     * Trie les types et retourne le type trouvé par corrélation avec les infos de la propriété
     * @param array $propertyConfig
     * @return array
     */
    public static function buildPropertyTypes(array $propertyConfig): array
    {
        ConfigurationTypes::getTypesOrderedByPriorities();
        return ConfigurationTypes::getGuessedType($propertyConfig);
    }
}