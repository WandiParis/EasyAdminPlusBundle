<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Wandi\EasyAdminPlusBundle\Generator\Exception\EAException;

class GeneratorGenerateCommand extends ContainerAwareCommand
{
    protected function configure(): void
    {
        $this
            ->setName('wandi:easy-admin-plus:generator:generate')
            ->setDescription('Create easy admin config files')
            ->setDefinition(
                new InputDefinition(array(
                    new InputOption('force', 'f'),
                ))
            )
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $container = $this->getContainer();
        $dirProject = $container->getParameter('kernel.project_dir');
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('A easy admin config file, <info>already exist</info>, do you want to <info>override</info> it [<info>y</info>/n]?', true);
        $cleanCommand = $this->getApplication()->find('wandi:easy-admin-plus:generator:cleanup');

        if (!$input->getOption('force')) {
            if (is_dir($dirProject.'/config/packages/easy_admin/')) {
                if (!$helper->ask($input, $output, $question)) {
                    return;
                }
            }
        }

        if (!is_dir($dirProject.'/config/packages/easy_admin/')) {
            if (mkdir($dirProject.'/config/packages/easy_admin/')) {
                $output->writeln('<info>easy_admin folder created successfully.</info>');
            } else {
                $output->writeln('<error>Unable to create easy_admin folder, the build process is stopped</error>');
            }
        } else {
            $cleanCommand->run(new ArrayInput([]), $output);
        }

        try {
            $eaTool = $container->get('wandi.easy_admin_plus.generator.generate');
            $eaTool->run();
        } catch (EAException $e) {
            $output->writeln('<error>(EAException catché)'.$e->getMessage().'</error>');
        }
    }
}
