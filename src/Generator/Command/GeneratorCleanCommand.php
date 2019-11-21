<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Command;

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

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $container = $this->getContainer();
        $dirProject = $container->getParameter('kernel.project_dir');

        if (!is_dir($dirProject.'/config/packages/easy_admin')) {
            throw new \RuntimeException('Unable to clean easy admin configuration, no configuration file found.');
        }

        $eaTool = $container->get('wandi.easy_admin_plus.generator.clean');
        $eaTool->run();
    }
}
