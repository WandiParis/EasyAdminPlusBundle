<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Wandi\EasyAdminPlusBundle\Generator\Model\Entity;
use Wandi\EasyAdminPlusBundle\Generator\Model\Action;
use Wandi\EasyAdminPlusBundle\Generator\Model\Field;
use Wandi\EasyAdminPlusBundle\Generator\Model\Method;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Wandi\EasyAdminPlusBundle\Generator\Property\PropertyConfig;
use Wandi\EasyAdminPlusBundle\Generator\Helper\PropertyClassHelper;
use Wandi\EasyAdminPlusBundle\Generator\Helper\PropertyTypeHelper;
use Wandi\I18nBundle\Traits\TranslatableEntity;
use Wandi\EasyAdminPlusBundle\Generator\GeneratorTool;

class FieldCreation
{
    public function run(Entity $entity, Method $method, array $propertyConfig): Field
    {
        $field = (new Field())
            ->setName($propertyConfig['name'])
            ->setType($propertyConfig['typeConfig']['easyAdminType'])
            ->setLabel($propertyConfig['name']);

        if ($propertyConfig['typeConfig']['typeForced'] && (empty($propertyConfig['typeConfig']['methodsTypeForced'])
                || !in_array($method->getName(), $propertyConfig['typeConfig']['methodsTypeForced']))) {
            $field->setForcedType($field->getType());
        }

        $this->buildFieldClassHelpers($field, $propertyConfig, $entity, $method);
        $this->buildFieldTypeHelpers($field, $propertyConfig, $method);

        return $field;

    }

    private function buildFieldTypeHelpers(Field $field, array $propertyConfig, Method $method): void
    {
        $helpers = PropertyTypeHelper::getTypeHelpers();

        foreach ($helpers as $type => $helper) {
            $helper = array_replace(PropertyTypeHelper::getMaskHelper(), $helper);

            if ($propertyConfig['typeConfig']['easyAdminType'] == $type && !in_array($method->getName(), $helper['methods'])) {
                PropertyTypeHelper::{$helper['function']}($propertyConfig, $field, $method);
            }
        }
    }

    private function buildFieldClassHelpers(Field $field, array $propertyConfig, Entity $entity, method $method): void
    {
        $helpers = PropertyClassHelper::getClassHelpers();

        foreach ($propertyConfig['annotationClasses'] as $annotation) {
            if (($classHelper = $helpers[get_class($annotation)] ?? null) && (!in_array($method->getName(), ['list', 'show'])
                    || in_array($method->getName(), $classHelper['methods']))) {
                PropertyClassHelper::{$classHelper['function']}($annotation, $field, $entity, $method);
            }

            //Enum
            if ('Greg0ire\Enum\Bridge\Symfony\Validator\Constraint\Enum' === get_parent_class(get_class($annotation))) {
                PropertyClassHelper::HandleEnum($annotation, $field, $entity, $method);
            }
        }
    }
}
