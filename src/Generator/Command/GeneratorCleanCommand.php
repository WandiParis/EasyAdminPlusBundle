<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Wandi\EasyAdminPlusBundle\Generator\Service\GeneratorClean;

class GeneratorCleanCommand extends Command
{
    /** @var GeneratorClean $generatorClean */
    private $generatorClean;
    private $projectDir;

    public function __construct(GeneratorClean $geenratorClean, string $projectDir)
    {
        $this->generatorClean = $geenratorClean;
        $this->projectDir = $projectDir;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('wandi:easy-admin-plus:generator:cleanup')
            ->setDescription('Cleans easy admin configuration files for non-existing entities.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        if (!is_dir($this->projectDir.'/config/packages/easy_admin')) {
            throw new \RuntimeException('Unable to clean easy admin configuration, no configuration file found.');
        }

        $this->generatorClean->run();
    }
}
