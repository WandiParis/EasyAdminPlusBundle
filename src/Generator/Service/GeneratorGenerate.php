<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Symfony\Component\Console\Output\ConsoleOutput;
use Wandi\EasyAdminPlusBundle\Generator\Model\Entity;
use Wandi\EasyAdminPlusBundle\Generator\GeneratorTool;
use Wandi\EasyAdminPlusBundle\Generator\Property\Initialization;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GeneratorGenerate extends GeneratorBase
{
    private $consoleOutput;
    private $propertyInitialization;
    private $entityCreation;

    public function __construct(
        EntityCreation $entityCreation,
        EntityManagerInterface $em,
        ParameterBagInterface $parameterBag
    ){
        $this->consoleOutput = new ConsoleOutput();
        $this->entityCreation = $entityCreation;

        parent::__construct($em, $parameterBag);
    }

    /**
     * Generates entity files, the menu file, and the base file.
     */
    public function run(): void
    {
        $listMetaData = $this->em->getMetadataFactory()->getAllMetadata();
        $locale = $this->parameterBag->get('locale') ?? $this->parameterBag->get('kernel.default_locale');

        $generatorTool = new GeneratorTool($this->generatorParameters);
        $generatorTool->setParameterBag($this->parameterBag->all());
        $generatorTool->initTranslation($this->generatorParameters['translation_domain'], $this->projectDir, $locale);
        $bundles = $this->parameterBag->get('kernel.bundles');

        if (empty($listMetaData)) {
            $this->consoleOutput->writeln('<comment>There are no entities to configure, the generation process is stopped.</comment>');

            return;
        }

        foreach ($listMetaData as $metaData) {
            if (null !== $entity = $this->entityCreation->run($metaData)) {
                $generatorTool->addEntity($entity);
            }
        }

        $generatorTool->generateMenuFile($this->projectDir, $this->consoleOutput);
        $generatorTool->generateEntityFiles($this->projectDir, $this->consoleOutput);
        $generatorTool->generateDesignFile($this->projectDir, $this->consoleOutput);
        $generatorTool->generateBaseFile($this->projectDir, $this->consoleOutput);
    }
}
