<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Wandi\EasyAdminPlusBundle\Generator\Model\Entity;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Wandi\EasyAdminPlusBundle\Generator\Property\PropertyConfig;
use Wandi\I18nBundle\Traits\TranslatableEntity;

class EntityCreation
{
    /** @var ParameterBagInterface $parameterBag */
    protected $parameterBag;
    /** @var MethodCreation $methodCreation */
    private $methodCreation;
    /** @var EntityPropertiesCreation $entityPropertiesCreation */
    private $entityPropertiesCreation;

    public function __construct(
        MethodCreation $methodCreation,
        EntityPropertiesCreation $entityPropertiesCreation,
        ParameterBagInterface $parameterBag
    ){
        $this->parameterBag = $parameterBag;
        $this->methodCreation = $methodCreation;
        $this->entityPropertiesCreation = $entityPropertiesCreation;
    }

    public function run(ClassMetadata $metaData): ?Entity
    {
        $nameData = self::buildNameData($metaData, $this->parameterBag->get('kernel.bundles'));
        $generatorParameters = $this->parameterBag->get('easy_admin_plus')['generator'];

        if (in_array(
            $nameData['bundle'].'Bundle',
            $generatorParameters['bundles_filter'])) {
            return null;
        }

        $entity = (new Entity($metaData))
            ->setName(Entity::buildName($nameData))
            ->setClass($metaData->getName())
            ->setProperties($this->entityPropertiesCreation->run($metaData));
        $entity->setMethods($this->methodCreation->run($entity, $generatorParameters));

        return $entity;
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
}
