<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Wandi\EasyAdminPlusBundle\Generator\Model\Entity;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Wandi\EasyAdminPlusBundle\Generator\Property\PropertyConfig;
use Wandi\I18nBundle\Traits\TranslatableEntity;

class EntityPropertiesCreation
{
    CONST TIMESTAMPABLE_TRAIT_NAME = 'Gedmo\Timestampable\Traits\Timestampable';
    CONST SOFTDELETEABLE_TRAIT_NAME = 'Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity';
    CONST TRANSLATABLE_TRAIT_NAME = 'Wandi\I18nBundle\Traits\TranslatableEntity';

    CONST STOF_DOCTRINE_BUNDLE_NAME = 'StofDoctrineExtensionsBundle';
    CONST VICH_UPLOADER_BUNDLE_NAME = 'VichUploaderBundle';
    CONST WANDI_I18N_BUNDLE_NAME = 'WandiI18nBundle';

    /** @var ParameterBagInterface $parameterBag */
    protected $bundles;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->bundles = $parameterBag->get('kernel.bundles');

    }

    public function run(ClassMetadata $metaData): array
    {
        $properties = [];
        $reflectionClass = new \ReflectionClass($metaData->getName());
        $classTraits = array_keys($reflectionClass->getTraits());

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $properties[] = PropertyConfig::setPropertyConfig($reflectionProperty);
        }

        if (array_key_exists(self::VICH_UPLOADER_BUNDLE_NAME, $this->bundles)) {
            PropertyConfig::setVichPropertiesConfig($properties);
        }

        if (array_key_exists(self::STOF_DOCTRINE_BUNDLE_NAME, $this->bundles)) {
            if (in_array(self::TIMESTAMPABLE_TRAIT_NAME, $classTraits)) {
                PropertyConfig::setTimestampablePropertiesConfig($properties);
            }

            if (in_array(self::SOFTDELETEABLE_TRAIT_NAME, $classTraits)) {
                PropertyConfig::setSoftDeleteableEntityPropertiesConfig($properties);
            }
        }

        if (array_key_exists(self::WANDI_I18N_BUNDLE_NAME, $this->bundles)) {
            if (in_array(self::TRANSLATABLE_TRAIT_NAME, $classTraits)) {
                PropertyConfig::setTranslatableEntityPropertiesConfig($properties);
            }
        }

        return $properties;
    }
}
