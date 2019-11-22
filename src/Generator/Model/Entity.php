<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

class Entity
{
    private $name;
    private $class;
    private $disabledAction;
    private $methods;
    private $properties;
    private $metaData;

    public function __construct(ClassMetadata $metaData)
    {
        $this->methods = new ArrayCollection();
        $this->disabledAction = [];
        $this->metaData = $metaData;
        $this->properties = [];
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

    public static function buildName(array $nameData): string
    {
        return strtolower($nameData['bundle'].'_'.$nameData['entity']);
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass($class): self
    {
        $this->class = $class;

        return $this;
    }

    public function getDisabledAction(): array
    {
        return $this->disabledAction;
    }

    public function setDisabledAction($disabledAction): self
    {
        $this->disabledAction = $disabledAction;

        return $this;
    }

    public function getMethods(): ArrayCollection
    {
        return $this->methods;
    }

    public function setMethods(ArrayCollection $methods): self
    {
        $this->methods = $methods;

        return $this;
    }

    public function buildMethods(array $eaToolParams): void
    {
        foreach ($eaToolParams['methods'] as $name => $method) {
            $method = new Method();
            $method->setName($name);
            $method->buildTitle($this->name);

            foreach ($eaToolParams['methods'][$name] as $actionName) {
                $action = new Action();
                $action->setName($actionName);
                $action->setIcon($action->getIconFromAction($eaToolParams['icons']['actions']));
                $action->setLabel($actionName);
                $method->addAction($action);
            }

            foreach ($this->properties as $property) {
                if (in_array($name, $property['typeConfig']['methodsNoAllowed'])) {
                    continue;
                }

                $field = new Field();
                $field->buildFieldConfig($property, $method);
                $field->buildFieldHelpers($property, $this, $method);
                $method->addField($field);
            }

            $this->addMethod($method);
        }
    }

    public function addMethod(Method $method): void
    {
        $this->methods[] = $method;
    }

    public function getStructure(array $eaToolParams): array
    {
        $methodsStructure = [];

        foreach ($this->methods as $method) {
            /** @var Method $method */
            $methodsStructure = array_merge($methodsStructure, $method->getStructure($eaToolParams));
        }

        $structure = [
            'easy_admin' => [
                'entities' => [
                    "$this->name" => array_merge([
                        'class' => $this->class,
                        'disabled_actions' => $this->disabledAction,
                    ], $methodsStructure),
                ],
            ],
        ];

        return $structure;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function setProperties(array $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    public function getMetaData(): ClassMetadata
    {
        return $this->metaData;
    }
}
