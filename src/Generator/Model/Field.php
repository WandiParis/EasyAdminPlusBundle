<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Model;

use Wandi\EasyAdminPlusBundle\Generator\Helper\PropertyClassHelper;
use Wandi\EasyAdminPlusBundle\Generator\Helper\PropertyTypeHelper;

class Field
{
    private $name;
    private $type;
    private $forcedType;
    private $typeOptions;
    private $label;
    private $help;
    private $format;
    private $template;
    private $propertyFile;

    public function __construct()
    {
        $this->typeOptions = ['attr' => []];
        $this->help = '';
        $this->forcedType = '';
        $this->format = '';
        $this->template = null;
        $this->propertyFile = '';
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName($name): Field
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): Field
    {
        $this->type = $type;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel($label): Field
    {
        $this->label = $label;

        return $this;
    }

    public function getStructure(): ?array
    {
        $structure = [
            'property' => $this->name,
            'label' => $this->name,
            'type' => $this->forcedType,
            'type_options' => $this->typeOptions,
            'help' => $this->help,
            'format' => $this->format,
            'template' => $this->template,
            'propertyFile' => $this->propertyFile,
        ];

        return self::removeEmptyValuesAndSubArrays($structure);
    }

    /**
     * Removes all empty sub array
     * Link: https://stackoverflow.com/a/46781625/7285018.
     */
    public static function removeEmptyValuesAndSubArrays(array $array): array
    {
        foreach ($array as $k => &$v) {
            if (is_array($v)) {
                $v = self::removeEmptyValuesAndSubArrays($v);
                if (!sizeof($v)) {
                    unset($array[$k]);
                }
            } elseif (!strlen($v) && false !== $v) {
                unset($array[$k]);
            }
        }

        return $array;
    }

    public function getTypeOptions(): ?array
    {
        return $this->typeOptions;
    }

    public function setTypeOptions(array $typeOptions): Field
    {
        $this->typeOptions = $typeOptions;

        return $this;
    }

    public function getHelp(): string
    {
        return $this->help;
    }

    public function setHelp(string $help): Field
    {
        $this->help = $help;

        return $this;
    }

    public function getForcedType(): string
    {
        return $this->forcedType;
    }

    public function setForcedType(string $forcedType): Field
    {
        $this->forcedType = $forcedType;

        return $this;
    }

    public function buildFieldConfig(array $propertyConfig, Method $method): void
    {
        $this->name = $propertyConfig['name'];
        $this->type = $propertyConfig['typeConfig']['easyAdminType'];
        $this->label = $propertyConfig['name'];

        if ($propertyConfig['typeConfig']['typeForced'] && (empty($propertyConfig['typeConfig']['methodsTypeForced'])
            || !in_array($method->getName(), $propertyConfig['typeConfig']['methodsTypeForced']))) {
            $this->forcedType = $this->type;
        }
    }

    private function buildFieldTypeHelpers(array $propertyConfig, Method $method): void
    {
        $helpers = PropertyTypeHelper::getTypeHelpers();

        foreach ($helpers as $type => $helper) {
            $helper = array_replace(PropertyTypeHelper::getMaskHelper(), $helper);

            if ($propertyConfig['typeConfig']['easyAdminType'] == $type && !in_array($method->getName(), $helper['methods'])) {
                PropertyTypeHelper::{$helper['function']}($propertyConfig, $this, $method);
            }
        }
    }

    private function buildFieldClassHelpers(array $propertyConfig, Entity $entity, method $method): void
    {
        $helpers = PropertyClassHelper::getClassHelpers();

        foreach ($propertyConfig['annotationClasses'] as $annotation) {
            if (($classHelper = $helpers[get_class($annotation)] ?? null) && (!in_array($method->getName(), ['list', 'show'])
                    || in_array($method->getName(), $classHelper['methods']))) {
                PropertyClassHelper::{$classHelper['function']}($annotation, $this, $entity, $method);
            }
        }
    }

    public function buildFieldHelpers(array $propertyConfig, Entity $entity, Method $method): void
    {
        $this->buildFieldClassHelpers($propertyConfig, $entity, $method);
        $this->buildFieldTypeHelpers($propertyConfig, $method);
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat($format): Field
    {
        $this->format = $format;

        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): Field
    {
        $this->template = $template;

        return $this;
    }

    public function getPropertyFile(): string
    {
        return $this->propertyFile;
    }

    public function setPropertyFile(?string $propertyFile): Field
    {
        $this->propertyFile = $propertyFile;

        return $this;
    }
}
