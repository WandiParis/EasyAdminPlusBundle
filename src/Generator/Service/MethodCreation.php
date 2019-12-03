<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Wandi\EasyAdminPlusBundle\Generator\Model\Entity;
use Wandi\EasyAdminPlusBundle\Generator\Model\Action;
use Wandi\EasyAdminPlusBundle\Generator\Model\Field;
use Wandi\EasyAdminPlusBundle\Generator\Model\Method;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Wandi\EasyAdminPlusBundle\Generator\Property\PropertyConfig;
use Wandi\I18nBundle\Traits\TranslatableEntity;
use Wandi\EasyAdminPlusBundle\Generator\GeneratorTool;
use Wandi\EasyAdminPlusBundle\Generator\Helper\PropertyClassHelper;
use Wandi\EasyAdminPlusBundle\Generator\Helper\PropertyTypeHelper;

class MethodCreation
{
    /** @var ParameterBagInterface $parameterBag */
    private $parameterBag;
    /** @var FieldCreation $fieldCreation */
    private $fieldCreation;

    public function __construct(FieldCreation $fieldCreation, ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
        $this->fieldCreation = $fieldCreation;
    }

    public function run(Entity $entity, array $parameters): ArrayCollection
    {
        $methods = new ArrayCollection();

        foreach ($parameters['methods'] as $name => $detail) {
            $method = (new Method())
                ->setName($name)
                ->setTitle(self::buildTitle($name, $entity->getName()));

            foreach ($parameters['methods'][$name] as $actionName) {
                $action = (new Action())
                    ->setName($actionName)
                    ->setIcon($parameters['icons']['actions'][$actionName] ?? '')
                    ->setLabel($actionName);

                $method->addAction($action);
            }

            foreach ($entity->getProperties() as $property) {
                if (in_array($name, $property['typeConfig']['methodsNoAllowed'])) {
                    continue;
                }

                $field = $this->fieldCreation->run($entity, $method, $property);

                $method->addField($this->fieldCreation->run($entity, $method, $property));
            }

            $methods->add($method);
        }

        return $methods;
    }

    /**
     * Construct the title of the method with the name of the entity (remove the prefix).
     */
    public function buildTitle(string $methodName, string $entityName): string
    {
        /** @var Translator $translator */
        $translator = GeneratorTool::getTranslation();

        $splitName = explode('_', $entityName);

        if (empty($splitName) || in_array($entityName, $splitName) || count($splitName) < 2) {
            $title = $entityName;
        } else {
            unset($splitName[0]);
            $title = implode(' ', $splitName);
        }

        return $translator->trans('generator.method.title.'.$methodName, ['%entity%' => $title]);
    }
}
