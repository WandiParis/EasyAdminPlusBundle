<?php

namespace Wandi\EasyAdminPlusBundle\Command;

use Wandi\EasyAdminPlusBundle\Generator\Exception\EAException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GeneratorCleanCommand extends ContainerAwareCommand
{
    protected function configure(): void
    {
        $this
            ->setName('wandi:easy-admin-plus:generator:cleanup')
            ->setDescription('Cleans easy admin configuration files for non-existing entities.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $container = $this->getContainer();
        $dirProject = $container->getParameter('kernel.project_dir');
        $generatorParams = $container->getParameter('easy_admin_plus')['generator'];

        if (!file_exists($dirProject . '/app/config/easyadmin/' . $generatorParams['pattern_file'] . '.yml'))
        {
            $output->writeln('<info>Unable</info> to clean easy admin configuration, no configuration file found.');
            $output->writeln('The cleaning process is stopped.');
            return ;
        }

        try {
            $eaTool = $container->get('easy_admin_plus.generator.clean');
            $eaTool->run();
        } catch (EAException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}