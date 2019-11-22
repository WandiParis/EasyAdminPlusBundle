<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Model;

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

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(?string $class): self
    {
        $this->class = $class;

        return $this;
    }
}
