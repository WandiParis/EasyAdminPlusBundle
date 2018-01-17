<?php

namespace Wandi\EasyAdminPlusBundle\Command;

use Wandi\EasyAdminPlusBundle\Generator\Exception\EAException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;

class GeneratorEntityCommand extends ContainerAwareCommand
{
    protected function configure(): void
    {
        $this
            ->setName('wandi:easy-admin:generator:entity')
            ->setDescription('Create a specified entity file configuration for easy admin')
            ->setDefinition(
                new InputDefinition(array(
                    new InputOption('force', 'f')
                ))
            )
            ->addArgument('entity', InputArgument::IS_ARRAY, 'The entity name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $dirProject = $this->getContainer()->getParameter('kernel.project_dir');
        $eaToolParams = $this->getContainer()->getParameter('ea_tool');
        $entiyManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $helper = $this->getHelper('question');
        $entitiesRawName = $input->getArgument('entity');
        $entitiesMetaData = [];

        if (!file_exists($dirProject . '/app/config/easyadmin/' . $eaToolParams['pattern_file'] . '.yml'))
        {
            $output->writeln('You need to launch <info>wandi:easy-admin:generator:generate</info> command before launching this command.');
            return ;
        }

        foreach ($entitiesRawName as $entityRawName)
        {
            $entitySplit = explode(':', $entityRawName);
            if (empty($entitySplit) || in_array($entityRawName, $entitySplit) || count($entitySplit) != 2)
            {
                $output->writeln('<comment>You have to enter a valid entity name prefixed by the name of the bundle to which it belongs (ex: AppBundle:Image), ' . $entityRawName . ' is invalid <info>
the generation process is stopped</info></comment>');
                return ;
            }
            $entitiesMetaData[] = $entiyManager->getClassMetadata($entitySplit[0] . '\Entity\\' . $entitySplit[1]);
        }

        if (!$input->getOption('force'))
        {
            foreach ($entitiesMetaData as $entity)
            {
                if (file_exists($dirProject . '/app/config/easyadmin/config_easyadmin_' . $entity . '.yml'))
                {
                    $question = new ConfirmationQuestion(sprintf('A easy admin config file for %s, already exist, do you want to override it [<info>y</info>/n]?', $entity), true);
                    if (!$helper->ask($input, $output, $question))
                        return;
                }
            }
        }

        try {
            $eaTool = $this->getContainer()->get('easy_admin_plus.generator.entity');
            $eaTool->run($entitiesMetaData, $this);
        } catch (EAException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }

    }
}