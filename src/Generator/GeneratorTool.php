<?php

namespace Wandi\EasyAdminPlusBundle\Generator;

use Wandi\EasyAdminPlusBundle\Generator\Exception\EAException;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Wandi\EasyAdminPlusBundle\Generator\Helper\PropertyClassHelper;
use Wandi\EasyAdminPlusBundle\Generator\Helper\PropertyTypeHelper;
use Wandi\EasyAdminPlusBundle\Generator\Model\Entity;

class GeneratorTool
{
    private $parameters = [];
    private $entities;
    private static $translation = null;
    private static $parameterBag = null;

    /**
     * EATool constructor.
     */
    public function __construct($parameters)
    {
        $this->entities = new ArrayCollection();
        $this->parameters = $parameters;
        $this->initHelpers();
    }

    /**
     * @return null|Translator
     */
    public static function getTranslation(): ?Translator
    {
        return self::$translation;
    }

    /**
     * @return array|null
     */
    public static function getParameterBag(): ?array
    {
        return self::$parameterBag;
    }

    /**
     * @param array $parameterBag
     */
    public static function setParameterBag(array $parameterBag): void
    {
        self::$parameterBag = $parameterBag;
    }

    /**
     * @return ArrayCollection
     */
    public function getEntities(): ArrayCollection
    {
        return $this->entities;
    }

    /**
     * @param ArrayCollection $entities
     *
     * @return GeneratorTool
     */
    public function setEntities(ArrayCollection $entities): GeneratorTool
    {
        $this->entities = $entities;

        return $this;
    }

    /**
     * @param Entity $entity
     *
     * @return $this
     */
    public function addEntity(Entity $entity): GeneratorTool
    {
        $this->entities[] = $entity;

        return $this;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     *
     * @return $this
     */
    public function setParameters(array $parameters): GeneratorTool
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getMenuStructure(): array
    {
        $entitiesMenuStructure = [];

        foreach ($this->getEntities()->getIterator()  as $entity) {
            $entitiesMenuStructure[] = self::buildEntryMenu($entity->getName());
        }

        $structure = [
            'easy_admin' => [
                'design' => [
                    'menu' => $entitiesMenuStructure,
                ],
            ],
        ];

        return $structure;
    }

    /**
     * @param $fileName
     * @param $projectDir
     */
    public function initTranslation(string $fileName, string $projectDir, string $userLocale): void
    {
        if (null === self::$translation) {
            self::$translation = new Translator($userLocale);
            self::$translation->addLoader('yaml', new YamlFileLoader());
            self::$translation->addResource('yaml', $projectDir.'/vendor/wandi/easyadmin-plus-bundle/src/Resources/translations/'.$fileName.'.'.$userLocale.'.yaml', $userLocale);
        }
    }

    public function initHelpers(): void
    {
        $classHelpers = array_map(function ($helper) {
            return array_replace(PropertyClassHelper::getMaskHelper(), $helper);
        }, PropertyClassHelper::getClassHelpers());

        PropertyClassHelper::setClassHelpers($classHelpers);

        $typeHelpers = array_map(function ($helper) {
            return array_replace(PropertyTypeHelper::getMaskHelper(), $helper);
        }, PropertyTypeHelper::getTypeHelpers());

        PropertyTypeHelper::setTypeHelpers($typeHelpers);
    }

    /**
     * @param $projectDir
     * @param ConsoleOutput $consoleOutput
     *
     * @throws EAException
     */
    public function generateMenuFile(string $projectDir, ConsoleOutput $consoleOutput): void
    {
        $ymlContent = self::buildDumpPhpToYml($this->getMenuStructure(), $this->parameters);
        $path = '/config/packages/easy_admin/menu.yaml';
        if (false !== file_put_contents($projectDir.$path, $ymlContent)) {
            $consoleOutput->writeln('The menu file <comment>'.$path.' </comment>has been <info>generated</info>.');
        } else {
            throw new EAException('Unable to generate the menu file, the generation process is <info>stopped</info>');
        }
    }

    /**
     * @param $projectDir
     * @param ConsoleOutput $consoleOutput
     *
     * @throws EAException
     */
    public function generateEntityFiles(string $projectDir, ConsoleOutput $consoleOutput): void
    {
        if (!is_dir($projectDir.'/config/packages/easy_admin/entities/')) {
            if (mkdir($projectDir.'/config/packages/easy_admin/entities/')) {
                $consoleOutput->writeln('<info>entities folder created successfully.</info>');
            } else {
                throw new EAException('the entity folder could not be created');
            }
        }

        foreach ($this->getEntities()->getIterator()  as $entity) {
            $ymlContent = self::buildDumpPhpToYml($entity->getStructure($this->parameters), $this->parameters);
            $path = '/config/packages/easy_admin/entities/'.$entity->getName().'.yaml';
            $consoleOutput->writeln('Generating entity "<info>'.$entity->getName().'</info>"');
            $this->createBackupFile($entity->getName(), $projectDir.$path, $consoleOutput);

            if (file_put_contents($projectDir.$path, $ymlContent)) {
                $consoleOutput->writeln('  > generating <comment>'.$path.'</comment>');
            } else {
                throw new EAException('Unable to generate the configuration file for the '.$entity->getName().' entity, the generation process is stopped');
            }
        }
    }

    /**
     * @return array
     */
    private function getBaseFileStructure(): array
    {
        $importFiles = [];

        foreach ($this->getEntities()->getIterator()  as $entity) {
            $importFiles[] = ['resource' => 'easy_admin/entities/'.$entity->getName().'.yaml'];
        }

        $importFiles[] = ['resource' => 'easy_admin/menu.yaml'];
        $importFiles[] = ['resource' => 'easy_admin/design.yaml'];

        return [
            'imports' => $importFiles,
        ];
    }

    /**
     * @return array
     */
    private function getDesignFileStructure(): array
    {
        $structure = [
            'easy_admin' => [
                'formats' => [
                    'datetime' => 'd/m/Y à H\hi',
                    'date' => 'd/m/Y',
                    'time' => 'H\hi e',
                ],
                'site_name' => $this->parameters['name_backend'],
                'design' => [
                    'brand_color' => '#D9262D',
                    'assets' => [
                        'js' => $this->parameters['assets']['js'],
                    ],
                ],
            ],
        ];

        return $structure;
    }

    public function generateDesignFile(string $projectDir, ConsoleOutput $consoleOutput): void
    {
        $ymlContent = self::buildDumpPhpToYml($this->getDesignFileStructure(), $this->parameters);
        $path = '/config/packages/easy_admin/design.yaml';

        if (file_put_contents($projectDir.$path, $ymlContent)) {
            $consoleOutput->writeln('The <info>design</info>  file <comment>'.$path.'</comment> has been <info>generated</info>.');
        } else {
            throw new EAException('Unable to generate the design file , the generation process is <info>stopped</info>');
        }
    }

    /**
     * @param $projectDir
     * @param ConsoleOutput $consoleOutput
     *
     * @throws EAException
     */
    public function generateBaseFile(string $projectDir, ConsoleOutput $consoleOutput): void
    {
        $ymlContent = self::buildDumpPhpToYml($this->getBaseFileStructure(), $this->parameters);
        $path = '/config/packages/easy_admin.yaml';

        $this->createBackupFile('easy_admin.yaml', $projectDir.$path, $consoleOutput);

        if (file_put_contents($projectDir.$path, $ymlContent)) {
            $consoleOutput->writeln('The <info>main</info> configuration file <comment>'.$path.'</comment> has been <info>generated</info>.');
        } else {
            throw new EAException('Unable to generate the main configuration file , the generation process is <info>stopped</info>');
        }
    }

    /**
     * Dumps a PHP value to YML.
     *
     * @param array $phpContent
     * @param array $parameters
     *
     * @return string
     */
    public static function buildDumpPhpToYml(array $phpContent, array $parameters): string
    {
        $dumper = new Dumper($parameters['dump_indentation']);
        $yml = $dumper->dump($phpContent, $parameters['dump_inline'], 0, Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE);

        return $yml;
    }

    public static function buildEntryMenu(string $nameEntity)
    {
        return [
            'entity' => $nameEntity,
            'label' => str_replace('_', ' ', preg_replace('/(?<! )(?<!^)[A-Z]/', ' $0', $nameEntity)),
        ];
    }

    public function createBackupFile($fileName, $filePath, ConsoleOutput $consoleOutput)
    {
        if (file_exists($filePath)) {
            if (true === copy($filePath, $filePath.'~')) {
                $consoleOutput->writeln('  > Backing up <comment>'.$fileName.'.php</comment> to <comment>'.$fileName.'.php~</comment>');
            } else {
                $consoleOutput->writeln('<error>Unable to copy the , ('.$filePath.') file</error>');
            }
        }
    }
}
