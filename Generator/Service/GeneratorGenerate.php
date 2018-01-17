<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Service;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Symfony\Component\Console\Output\ConsoleOutput;
use Wandi\EasyAdminPlusBundle\Generator\Entity;
use Wandi\EasyAdminPlusBundle\Generator\EATool;
use Wandi\EasyAdminPlusBundle\Generator\GeneratorConfigInterface;

class GeneratorGenerate extends GeneratorBase implements GeneratorConfigInterface
{
    private $vichMappings;
    private $consoleOutput;

    public function buildServiceConfig()
    {
        $this->vichMappings = $this->container->getParameter('vich_uploader.mappings');
        $this->consoleOutput = new ConsoleOutput();
    }

    /**
     * Génère les fichiers d'entités, le fichier du menu et le fichier de base
     * @throws \Wandi\EasyAdminPlusBundle\Generator\Exception\EAException
     */
    public function run(): void
    {
        $listMetaData = $this->em->getMetadataFactory()->getAllMetadata();

        $eaTool = new EATool($this->parameters);
        $eaTool->setParameterBag($this->container->getParameterBag()->all());
        $eaTool->initTranslation($this->parameters['translation_domain'], $this->projectDir);
        $bundles = $this->container->getParameter('kernel.bundles');

        if (empty($listMetaData))
        {
            $this->consoleOutput->writeln('<comment>There are no entities to configure, the generation process is stopped.</comment>');
            return ;
        }

        foreach ($listMetaData as $metaData)
        {
            $nameData = Entity::buildNameData($metaData, $bundles);
            if (in_array($nameData['bundle']."Bundle", $this->parameters['bundles_filter']))
                continue ;

            /** @var ClassMetadata $metaData */
            $entity = new Entity($metaData);
            $entity->setName(Entity::buildName($nameData));
            $entity->setClass($metaData->getName());
            $entity->buildMethods($eaTool->getParameters());

            $eaTool->addEntity($entity);
        }

        $eaTool->generateMenuFile($this->projectDir, $this->consoleOutput);
        $eaTool->generateEntityFiles($this->projectDir, $this->consoleOutput);
        $eaTool->generateBaseFile($this->projectDir, $this->consoleOutput);
    }
}