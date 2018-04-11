<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Service;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Symfony\Component\Console\Output\ConsoleOutput;
use Wandi\EasyAdminPlusBundle\Generator\Model\Entity;
use Wandi\EasyAdminPlusBundle\Generator\GeneratorTool;

class GeneratorGenerate extends GeneratorBase implements GeneratorConfigInterface
{
    private $vichMappings;
    private $consoleOutput;

    /**
     * @required
     */
    public function buildServiceConfig()
    {
        $this->vichMappings = $this->container->hasParameter('vich_uploader.mappings') ?
            $this->container->getParameter('vich_uploader.mappings') : null;
        $this->consoleOutput = new ConsoleOutput();
    }

    /**
     * Generates entity files, the menu file, and the base file.
     *
     * @throws \Wandi\EasyAdminPlusBundle\Generator\Exception\EAException
     */
    public function run(): void
    {
        $listMetaData = $this->em->getMetadataFactory()->getAllMetadata();
        $locale = $this->container->getParameter('locale') ?? $this->container->getParameter('kernel.default_locale');

        $generatorTool = new GeneratorTool($this->parameters);
        $generatorTool->setParameterBag($this->container->getParameterBag()->all());
        $generatorTool->initTranslation($this->parameters['translation_domain'], $this->projectDir, $locale);
        $bundles = $this->container->getParameter('kernel.bundles');

        if (empty($listMetaData)) {
            $this->consoleOutput->writeln('<comment>There are no entities to configure, the generation process is stopped.</comment>');

            return;
        }

        foreach ($listMetaData as $metaData) {
            $nameData = Entity::buildNameData($metaData, $bundles);
            if (in_array($nameData['bundle'].'Bundle', $this->parameters['bundles_filter'])) {
                continue;
            }

            /** @var ClassMetadata $metaData */
            $entity = new Entity($metaData);
            $entity->setName(Entity::buildName($nameData));
            $entity->setClass($metaData->getName());
            $entity->buildMethods($generatorTool->getParameters());

            $generatorTool->addEntity($entity);
        }

        $generatorTool->generateMenuFile($this->projectDir, $this->consoleOutput);
        $generatorTool->generateEntityFiles($this->projectDir, $this->consoleOutput);
        $generatorTool->generateDesignFile($this->projectDir, $this->consoleOutput);
        $generatorTool->generateBaseFile($this->projectDir, $this->consoleOutput);
    }
}
