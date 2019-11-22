<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wandi\EasyAdminPlusBundle\Generator\Exception\RuntimeCommandException;
use Symfony\Component\Console\Command\Command;
use Wandi\EasyAdminPlusBundle\Generator\Service\GeneratorGenerate;

class GeneratorGenerateCommand extends Command
{
    /** @var SymfonyStyle $io */
    private $io;
    /** @var GeneratorGenerate $generator */
    private $generator;
    private $projectDir;

    public function __construct(GeneratorGenerate $generator, string $projectDir)
    {
        $this->generator = $generator;
        $this->projectDir = $projectDir;

        parent::__construct();
    }

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

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('A easy admin config file, <info>already exist</info>, do you want to <info>override</info> it [<info>y</info>/n]?', true);
        $cleanCommand = $this->getApplication()->find('wandi:easy-admin-plus:generator:cleanup');

        if (!$input->getOption('force')) {
            if (is_dir($this->projectDir.'/config/packages/easy_admin/')) {
                if (!$helper->ask($input, $output, $question)) {
                    return;
                }
            }
        }

        if (!is_dir($this->projectDir.'/config/packages/easy_admin/')) {
            if (!mkdir($this->projectDir.'/config/packages/easy_admin/')) {
                throw new RuntimeCommandException('Unable to create easy_admin folder, the build process is stopped');
            }

            $this->io->success('easy_admin folder created successfully.');
        } else {
            $cleanCommand->run(new ArrayInput([]), $output);
        }

        $this->generator->run();

    }
}
