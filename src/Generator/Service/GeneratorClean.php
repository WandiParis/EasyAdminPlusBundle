<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Service;

use Wandi\EasyAdminPlusBundle\Generator\EATool;
use Wandi\EasyAdminPlusBundle\Generator\Entity;
use Wandi\EasyAdminPlusBundle\Generator\Exception\EAException;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Yaml\Yaml;

class GeneratorClean
{
    private $parameters;
    private $em;
    private $projectDir;
    private $consoleOutput;
    private $bundles;

    /**
     * GeneratorClean constructor.
     * @param EntityManager $entityManager
     * @param $parameters
     * @param $projectDir
     * @param $bundles
     */
    public function __construct(EntityManager $entityManager, $parameters, $projectDir, $bundles)
    {
        $this->em = $entityManager;
        $this->parameters = $parameters;
        $this->projectDir = $projectDir;
        $this->consoleOutput = new ConsoleOutput();
        $this->bundles = $bundles;
    }

    /**
     * Suppression des entités dans menu, dans la liste des ficheirs importés et du fichier en lui même
     * TODO: Mettre les methodes de purges dans une autre classe
     *
     * @throws EAException
     */
    public function run(): void
    {
        $fileContent = Yaml::parse(file_get_contents($this->projectDir . '/config/packages/easy_admin.yaml'));
        if (!isset($fileContent['imports']))
            throw new EAException('There are no imported files.');

        $entitiesToDelete = $this->getEntitiesToDelete($fileContent);

        if (empty($entitiesToDelete))
        {
            $this->consoleOutput->writeln('There are no files to clean, cleaning process <info>completed</info>.');
            return ;
        }

        $this->consoleOutput->writeln('<info>Start </info>of cleaning easyadmin configuration files.');
        $this->purgeImportedFiles($entitiesToDelete);
        $this->purgeEasyAdminMenu($entitiesToDelete);
        $this->purgeEntityFiles($entitiesToDelete);
        $this->consoleOutput->writeln('Cleaning process <info>completed</info>');
    }

    /**
     * Retourne la liste des entités à supprimer
     * @param $fileContent
     * @return array
     */
    private function getEntitiesToDelete($fileContent): array
    {
        $entitiesToDelete = [];
        $entitiesList = $this->getEntitiesNameFromMetaDataList($this->em->getMetadataFactory()->getAllMetadata(), $this->bundles);
        $entitiesEasyAdmin = $this->getNameListEntities($fileContent['imports']);

        foreach (array_diff($entitiesEasyAdmin, $entitiesList) as $entity)
        {
            $entitiesToDelete['name'][] = $entity;
            $entitiesToDelete['pattern'][] = 'wandi_easy_admin_plus/' . $this->parameters['pattern_file'] . '_' . $entity . '.yaml';
        }
        return $entitiesToDelete;
    }

    /**
     * Recupère le nom des entités à partir des noms des fichiers importés
     * TODO: Récupérer les noms des entités à partir du menu ou d'un tableau généré
     * @param array $files
     * @return array
     */
    private function getNameListEntities(array $files): array
    {
        $entitiesName = [];

        foreach ($files as $fileName)
        {
            if ($fileName['resource'] === 'wandi_easy_admin_plus/' . $this->parameters['pattern_file'] . '_menu.yaml')
                continue ;
            $lengthPattern = strlen('wandi_easy_admin_plus/' . $this->parameters['pattern_file']);
            $postPatternFile = strripos($fileName['resource'], 'wandi_easy_admin_plus/' . $this->parameters['pattern_file'] . '_');
            $entitiesName[] = substr($fileName['resource'], $postPatternFile + $lengthPattern + 1,  - 5 - $postPatternFile );
        }
        return $entitiesName;
    }

    /**
     * Retourne un tableau contenant les noms des entités
     * @param array $metaDataList
     * @param array $bundles
     * @return array
     */
    private function getEntitiesNameFromMetaDataList(array $metaDataList, array $bundles): array
    {
        $entitiesName = array_map(function($metaData) use ($bundles){
            return Entity::buildName(Entity::buildNameData($metaData, $bundles));
        }, $metaDataList);
        return $entitiesName;
    }

    /**
     * @param $entities
     * @throws EAException
     */
    private function purgeImportedFiles(array $entities): void
    {
        $fileBaseContent = Yaml::parse(file_get_contents(sprintf('%s/config/packages/easy_admin.yaml', $this->projectDir)));

        if (!isset($fileBaseContent['imports']))
        {
            throw new EAException('No imported files2');
        }

        foreach ($fileBaseContent['imports'] as $key => $import)
        {
            if (in_array($import['resource'], $entities['pattern']))
                unset($fileBaseContent['imports'][$key]);
        }

        $fileBaseContent['imports'] = array_values($fileBaseContent['imports']);
        $ymlContent = EATool::buildDumpPhpToYml($fileBaseContent, $this->parameters);
        file_put_contents(sprintf('%s/config/packages/easy_admin.yaml', $this->projectDir ,$this->parameters['pattern_file']), $ymlContent);
    }

    /**
     * @param $entities
     * @throws EAException
     */
    private function purgeEasyAdminMenu(array $entities): void
    {
        $fileContent = Yaml::parse(file_get_contents(sprintf( '%s/config/packages/wandi_easy_admin_plus/%s_menu.yaml', $this->projectDir, $this->parameters['pattern_file'])));

        if (!isset($fileContent['easy_admin']['design']['menu']))
        {
            throw new EAException('no easy admin menu detected');
        }

        foreach ($fileContent['easy_admin']['design']['menu'] as $key => $entry)
        {
            if (in_array($entry['entity'], $entities['name']))
                unset($fileContent['easy_admin']['design']['menu'][$key]);
        }

        $fileContent['easy_admin']['design']['menu'] = array_values($fileContent['easy_admin']['design']['menu']);
        $ymlContent = EATool::buildDumpPhpToYml($fileContent, $this->parameters);
        file_put_contents($this->projectDir . '/config/packages/wandi_easy_admin_plus/' . $this->parameters['pattern_file'] . '_menu.yaml', $ymlContent);
    }

    /**
     * @param $entities
     * @throws EAException
     */
    private function purgeEntityFiles(array $entities): void
    {
        foreach ($entities['name'] as $entityName)
        {
            $this->consoleOutput->writeln(sprintf('Purging entity <info>%s</info>',$entityName));
            $path = sprintf('/config/packages/wandi_easy_admin_plus/%s_%s.yaml', $this->parameters['pattern_file'], $entityName);
            if (unlink($this->projectDir . $path))
                $this->consoleOutput->writeln(sprintf('   >File <comment>%s</comment> has been <info>deleted</info>.', $path));
            else
                throw new EAException(sprintf('Unable to delete configuration file for %s entity', $entityName));
        }
    }
}
