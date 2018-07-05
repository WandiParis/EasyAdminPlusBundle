<?php

namespace Lle\EasyAdminPlusBundle\Generator\Model;

class Action
{
    private $name;
    private $icon;

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
            'icon' => $this->icon,
        ];
    }
}
