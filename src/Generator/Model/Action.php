<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Model;

class Action
{
    private $name;
    private $icon;
    private $label;

    public function __construct()
    {
        $this->label = '';
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getIconFromAction(array $parameters): string
    {
        return $parameters[$this->name] ?? '';
    }

    public function getStructure(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'icon' => $this->icon,
        ];
    }
}
