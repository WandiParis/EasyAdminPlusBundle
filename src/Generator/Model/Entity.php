<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Wandi\EasyAdminPlusBundle\Generator\Exception\EAException;
use Wandi\EasyAdminPlusBundle\Generator\Helper\PropertyTypeHelper;
use Wandi\EasyAdminPlusBundle\Generator\Property\PropertyConfig;

class Entity
{
    private $name;
    private $class;
    private $disabledAction;
    private $methods;
    private $properties;
    private $metaData;

    /**
     * Entity constructor.
     *
     * @param ClassMetadata $metaData
     *
     * @throws EAException
     */
    public function __construct(ClassMetadata $metaData)
    {
        $this->methods = new ArrayCollection();
        $this->disabledAction = [];
        $this->metaData = $metaData;
        $this->properties = [];

        $this->initProperties();
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
     * @return object Entity
     */
    public function setName(string $name): Entity
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param ClassMetadata $metaData
     * @param array         $bundles
     *
     * @return array
     *
     * @throws EAException
     */
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
            throw new EAException('Unable to parse the bundle name for the '.$entityShortName.' entity');
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

        throw new EAException('<comment>the entity bundle could not be found for the '.$entityShortName.'</comment>');
    }

    public static function buildName(array $nameData): string
    {
        return strtolower($nameData['bundle'].'_'.$nameData['entity']);
    }

    /**
     * @return mixed
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @param mixed $class
     *
     * @return $this
     */
    public function setClass($class): Entity
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDisabledAction(): array
    {
        return $this->disabledAction;
    }

    /**
     * @param mixed $disabledAction
     *
     * @return $this
     */
    public function setDisabledAction($disabledAction): Entity
    {
        $this->disabledAction = $disabledAction;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMethods(): ArrayCollection
    {
        return $this->methods;
    }

    /**
     * @param ArrayCollection $methods
     *
     * @return $this
     */
    public function setMethods(ArrayCollection $methods): Entity
    {
        $this->methods = $methods;

        return $this;
    }

    /**
     * @param $eaToolParams
     */
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

    /**
     * @param Method $method
     */
    public function addMethod(Method $method): void
    {
        $this->methods[] = $method;
    }

    /**
     * @param $eaToolParams
     *
     * @return array
     */
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

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param array $properties
     *
     * @return $this
     */
    public function setProperties(array $properties): Entity
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @throws EAException
     */
    private function initProperties(): void
    {
        $reflectionProperties = (new \ReflectionClass($this->metaData->getName()))->getProperties();

        foreach ($reflectionProperties as $reflectionProperty) {
            $this->properties[] = PropertyConfig::setPropertyConfig($reflectionProperty);
        }

        $this->properties = PropertyTypeHelper::setVichPropertiesConfig($this->properties);
    }

    /**
     * @return ClassMetadata
     */
    public function getMetaData(): ClassMetadata
    {
        return $this->metaData;
    }

    /**
     * @param array $metaData
     *
     * @return $this
     */
    public function setMetaData(array $metaData): Entity
    {
        $this->metaData = $metaData;

        return $this;
    }
}
