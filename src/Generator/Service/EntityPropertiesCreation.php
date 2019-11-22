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
    /** @var ParameterBagInterface $parameterBag */
    protected $parameterBag;
    private $properties;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
        $this->properties = [];
    }

    public function run(ClassMetadata $metaData): array
    {
        $properties = [];

        $reflectionClass = new \ReflectionClass($metaData->getName());
        $classTraits = array_keys($reflectionClass->getTraits());

        $timestampableClassName = 'Gedmo\Timestampable\Traits\Timestampable';
        $translatableEntityClassName = 'Wandi\I18nBundle\Traits\TranslatableEntity';
        $softDeleteableEntityClassName = 'Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity';

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $this->properties[] = PropertyConfig::setPropertyConfig($reflectionProperty);
        }

        foreach ($this->properties as $property) {
            dump(sprintf('propriété: %s - type: %s', $property['name'], $property['typeConfig']['easyAdminType']));
        }

        //Vich (Image/File)
        PropertyConfig::setVichPropertiesConfig($this->properties);

        foreach ($this->properties as $property) {
            dump(sprintf('propriété: %s - type: %s', $property['name'], $property['typeConfig']['easyAdminType']));
        }

        //Timestampable
        if (trait_exists($timestampableClassName) && in_array($timestampableClassName, $classTraits)) {
            PropertyConfig::setTimestampablePropertiesConfig($this->properties);
        }

        //TranslatableEntity
        if (trait_exists($translatableEntityClassName) && in_array($translatableEntityClassName, $classTraits)) {
            PropertyConfig::setTranslatableEntityPropertiesConfig($this->properties);
        }

        //SoftDeleteableEntity
        if (trait_exists($softDeleteableEntityClassName) && in_array($softDeleteableEntityClassName, $classTraits)) {
            PropertyConfig::setSoftDeleteableEntityPropertiesConfig($this->properties);
        }

        dump('------------');

        foreach ($this->properties as $property) {
            dump(sprintf('propriété: %s - type: %s', $property['name'], $property['typeConfig']['easyAdminType']));
        }

        $entity->setProperties($this->properties);

//        die;


        return $properties;
    }
}
