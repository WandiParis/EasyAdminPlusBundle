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
    private $basePath;
    private $format;

    /**
     * Field constructor.
     */
    public function __construct()
    {
        $this->typeOptions = ['attr' => []];
        $this->help = '';
        $this->basePath = '';
        $this->forcedType = '';
        $this->format = '';
    }

    /**
     * @return mixed
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return $this
     */
    public function setName($name): Field
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     *
     * @return $this
     */
    public function setType(string $type): Field
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     *
     * @return $this
     */
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
            'base_path' => $this->basePath,
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

    /**
     * @return array
     */
    public function getTypeOptions(): ?array
    {
        return $this->typeOptions;
    }

    /**
     * @param array $typeOptions
     *
     * @return $this
     */
    public function setTypeOptions(array $typeOptions): Field
    {
        $this->typeOptions = $typeOptions;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHelp(): string
    {
        return $this->help;
    }

    /**
     * @param mixed $help
     *
     * @return $this
     */
    public function setHelp(string $help): Field
    {
        $this->help = $help;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * @param mixed $basePath
     *
     * @return $this
     */
    public function setBasePath(string $basePath): Field
    {
        $this->basePath = $basePath;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getForcedType(): string
    {
        return $this->forcedType;
    }

    /**
     * @param mixed $forcedType
     *
     * @return $this
     */
    public function setForcedType(string $forcedType): Field
    {
        $this->forcedType = $forcedType;

        return $this;
    }

    /**
     * @param array  $propertyConfig
     * @param Method $method
     */
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

    /**
     * @param array  $propertyConfig
     * @param Method $method
     */
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

    /**
     * @param array  $propertyConfig
     * @param Entity $entity
     * @param method $method
     */
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

    /**
     * @param array  $propertyConfig
     * @param Entity $entity
     * @param Method $method
     */
    public function buildFieldHelpers(array $propertyConfig, Entity $entity, Method $method): void
    {
        $this->buildFieldClassHelpers($propertyConfig, $entity, $method);
        $this->buildFieldTypeHelpers($propertyConfig, $method);
    }

    /**
     * @return mixed
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @param mixed $format
     *
     * @return $this
     */
    public function setFormat($format): Field
    {
        $this->format = $format;

        return $this;
    }
}
