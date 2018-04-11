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

    /**
     * @return mixed
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return $this
     */
    public function setName(string $name): Action
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @param mixed $icon
     *
     * @return $this
     */
    public function setIcon(string $icon): Action
    {
        $this->icon = $icon;

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
    public function setLabel(string $label): Action
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @param array $parameters
     *
     * @return string
     */
    public function getIconFromAction(array $parameters): string
    {
        return $parameters[$this->name] ?? '';
    }

    /**
     * @return array
     */
    public function getStructure(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'icon' => $this->icon,
        ];
    }
}
