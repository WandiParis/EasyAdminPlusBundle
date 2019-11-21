<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Wandi\EasyAdminPlusBundle\Generator\Exception\RuntimeCommandException;
use Wandi\EasyAdminPlusBundle\Generator\Property\PropertyConfig;
use Wandi\I18nBundle\Traits\TranslatableEntity;

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

        $this->initProperties();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Entity
    {
        $this->name = $name;

        return $this;
    }

    public static function buildNameData(ClassMetadata $metaData, array $bundles): array
    {
        $entityShortName = (new \ReflectionClass($metaData->getName()))->getShortName();

        if ("App\Entity" == $metaData->namespace) {
            return[
                'bundle' => 'App',
                'entity' => $entityShortName,
            ];
        }

        if (0 === preg_match('#((.*?)(?:Bundle))#', $metaData->getName(), $match)) {
            throw new RuntimeCommandException('Unable to parse the bundle name for the '.$entityShortName.' entity');
        }

        unset($match[0]);
        $match = array_values($match);

        $match = array_map(function ($a) {
            return str_replace('\\', '', $a);
        }, $match);

        foreach ($bundles as $name => $bundle) {
            if ($match[0] === $name) {
                return [
                    'bundle' => str_replace('\\', '', $match[1]),
                    'entity' => $entityShortName,
                ];
            }
        }

        throw new RuntimeCommandException('<comment>the entity bundle could not be found for the '.$entityShortName.'</comment>');
    }

    public static function buildName(array $nameData): string
    {
        return strtolower($nameData['bundle'].'_'.$nameData['entity']);
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass($class): Entity
    {
        $this->class = $class;

        return $this;
    }

    public function getDisabledAction(): array
    {
        return $this->disabledAction;
    }

    public function setDisabledAction($disabledAction): Entity
    {
        $this->disabledAction = $disabledAction;

        return $this;
    }

    public function getMethods(): ArrayCollection
    {
        return $this->methods;
    }

    public function setMethods(ArrayCollection $methods): Entity
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

    public function setProperties(array $properties): Entity
    {
        $this->properties = $properties;

        return $this;
    }

    private function initProperties(): void
    {
        $reflectionClass = new \ReflectionClass($this->metaData->getName());
        $classTraits = array_keys($reflectionClass->getTraits());

        $timestampableClassName = 'Gedmo\Timestampable\Traits\Timestampable';
        $translatableEntityClassName = 'Wandi\I18nBundle\Traits\TranslatableEntity';

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $this->properties[] = PropertyConfig::setPropertyConfig($reflectionProperty);
        }

        //Vich (Image/File)
        PropertyConfig::setVichPropertiesConfig($this->properties);

        //Timestampable
        if (trait_exists($timestampableClassName) && in_array($timestampableClassName, $classTraits)) {
            PropertyConfig::setTimestampablePropertiesConfig($this->properties);
        }

        //TranslatableEntity
        if (trait_exists($translatableEntityClassName) && in_array($translatableEntityClassName, $classTraits)) {
            PropertyConfig::setTranslatableEntityPropertiesConfig($this->properties);
        }

        dump('------------');

        foreach ($this->properties as $property) {
            dump(sprintf('propriété: %s - type: %s', $property['name'], $property['typeConfig']['easyAdminType']));
        }

//        die;
    }

    public function getMetaData(): ClassMetadata
    {
        return $this->metaData;
    }

    public function setMetaData(array $metaData): Entity
    {
        $this->metaData = $metaData;

        return $this;
    }
}
