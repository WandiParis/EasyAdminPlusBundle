<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Command\Command;
use Wandi\EasyAdminPlusBundle\Generator\GeneratorTool;
use Wandi\EasyAdminPlusBundle\Generator\Model\Entity;
use Wandi\EasyAdminPlusBundle\Generator\Exception\EAException;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Yaml\Yaml;

class GeneratorEntity extends GeneratorBase implements GeneratorConfigInterface
{
    private $consoleOutput;

    public function buildServiceConfig()
    {
        $this->consoleOutput = new ConsoleOutput();
    }

    /**
     * TODO: Factoriser les fonctions generateFileEntity avec Eatool class.
     *
     * @param array   $entitiesMetaData
     * @param Command $command
     *
     * @throws EAException
     */
    public function run(array $entitiesMetaData, Command $command): void
    {
        $bundles = $this->container->getParameter('kernel.bundles');
        $locale = $this->container->getParameter('locale') ?? $this->container->getParameter('kernel.default_locale');
        $relatedEntities = $this->getRelatedEntitiesMetaData($entitiesMetaData, $command, $bundles);
        $relatedEntities = array_merge($relatedEntities, $entitiesMetaData);

        $generatorTool = new GeneratorTool($this->parameters);
        $generatorTool->setParameterBag($this->container->getParameterBag()->all());
        $generatorTool->initTranslation($this->parameters['translation_domain'], $this->projectDir, $locale);

        foreach ($relatedEntities as $entityMetaData) {
            $entity = new Entity($entityMetaData);
            $entity->setName(Entity::buildName(Entity::buildNameData($entityMetaData, $bundles)));
            $entity->setClass($entityMetaData->getName());
            $entity->buildMethods($this->parameters);
            $generatorTool->addEntity($entity);
        }

        $generatorTool->generateEntityFiles($this->projectDir, $this->consoleOutput);
        $this->updateMenuFile($generatorTool->getEntities());
        $this->updateImportsFile($generatorTool->getEntities());
    }

    /**
     * @param ArrayCollection $entities
     *
     * @throws EAException
     */
    private function updateMenuFile(ArrayCollection $entities): void
    {
        $fileMenuContent = Yaml::parse(file_get_contents(sprintf('%s/config/packages/easy_admin/menu.yaml', $this->projectDir)));

        if (!isset($fileMenuContent['easy_admin']['design']['menu'])) {
            throw new EAException('no easy admin menu detected');
        }

        foreach ($entities as $entity) {
            if (false === array_search($entity->getName(), array_column($fileMenuContent['easy_admin']['design']['menu'], 'entity'))) {
                $fileMenuContent['easy_admin']['design']['menu'][] = GeneratorTool::buildEntryMenu($entity->getName());
            }
        }

        $ymlContent = GeneratorTool::buildDumpPhpToYml($fileMenuContent, $this->parameters);
        file_put_contents($this->projectDir.'/config/packages/easy_admin/menu.yaml', $ymlContent);
    }

    /**
     * @param ArrayCollection $entities
     *
     * @throws EAException
     */
    private function updateImportsFile(ArrayCollection $entities): void
    {
        $fileMenuContent = Yaml::parse(file_get_contents(sprintf('%s/config/packages/easy_admin.yaml', $this->projectDir)));

        if (!isset($fileMenuContent['imports'])) {
            throw new EAException('There is no imports option in the configuration file.');
        }

        foreach ($entities as $entity) {
            $patternEntity = 'easy_admin/entities/'.$entity->getName().'.yaml';

            if (false === array_search($patternEntity, array_column($fileMenuContent['imports'], 'resource'))) {
                $fileMenuContent['imports'][] = [
                    'resource' => $patternEntity,
                ];
            }
        }

        $ymlContent = GeneratorTool::buildDumpPhpToYml($fileMenuContent, $this->parameters);
        if (!file_put_contents(sprintf('%s/config/packages/easy_admin.yaml', $this->projectDir), $ymlContent)) {
            throw new EAException(sprintf('Can not update imported files in %s/config/packages/easy_admin.yaml', $this->projectDir));
        }
    }

    private function getRelatedEntitiesMetaData(array $entitiesMetaData, Command $command, array $bundles): array
    {
        $listMetaData = $this->em->getMetadataFactory()->getAllMetadata();
        $relatedEntities = [
            'name' => [],
            'metaData' => [],
        ];
        $consoleInput = new ArgvInput();
        $consoleOutput = new ConsoleOutput();
        $helper = $command->getHelper('question');

        $entitiesName = array_map(function ($entityMetaData) {
            return $entityMetaData->getName();
        }, $entitiesMetaData);

        foreach ($listMetaData as $metaData) {
            if (empty($metaData->associationMappings)) {
                continue;
            }

            foreach ($metaData->associationMappings as $associationMapping) {
                if (in_array($associationMapping['targetEntity'], $entitiesName)) {
                    if (in_array($associationMapping['targetEntity'], $relatedEntities['name'])) {
                        continue;
                    }

                    $question = new ConfirmationQuestion(sprintf('The <info>%s</info> entity is linked, do you want to (re)generate its configuration file [<info>y</info>/n]?', $metaData->name), true);
                    if ($helper->ask($consoleInput, $consoleOutput, $question)) {
                        $relatedEntities['name'][] = $metaData->getName();
                        $relatedEntities['metaData'][] = $metaData;
                    }
                }
            }
        }

        return $relatedEntities['metaData'];
    }
}
