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
    private $class;

    public function __construct()
    {
        $this->typeOptions = ['attr' => []];
        $this->help = '';
        $this->forcedType = '';
        $this->format = '';
        $this->template = null;
        $this->propertyFile = null;
        $this->class = null;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel($label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getStructure(): ?array
    {
        $structure = [
            'property' => $this->name,
            'label' => $this->label ?? $this->name,
            'type' => $this->forcedType,
            'type_options' => $this->typeOptions,
            'help' => $this->help,
            'format' => $this->format,
            'template' => $this->template,
            'propertyFile' => $this->propertyFile,
            'class' => $this->getClass(),
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

    public function setTypeOptions(array $typeOptions): self
    {
        $this->typeOptions = $typeOptions;

        return $this;
    }

    public function getHelp(): string
    {
        return $this->help;
    }

    public function setHelp(string $help): self
    {
        $this->help = $help;

        return $this;
    }

    public function getForcedType(): string
    {
        return $this->forcedType;
    }

    public function setForcedType(string $forcedType): self
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

            //Enum
            if ('Greg0ire\Enum\Bridge\Symfony\Validator\Constraint\Enum' === get_parent_class(get_class($annotation))) {
                PropertyClassHelper::HandleEnum($annotation, $this, $entity, $method);
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

    public function setFormat($format): self
    {
        $this->format = $format;

        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function getPropertyFile(): string
    {
        return $this->propertyFile;
    }

    public function setPropertyFile(?string $propertyFile): self
    {
        $this->propertyFile = $propertyFile;

        return $this;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(?string $class): self
    {
        $this->class = $class;

        return $this;
    }
}
