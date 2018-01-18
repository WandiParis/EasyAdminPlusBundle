<?php

namespace Wandi\EasyAdminPlusBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wandi\EasyAdminPlusBundle\Entity\User;

class UserEnableCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('wandi:easy-admin-plus:user:enable')
            ->setDescription('Enable an admin')
            ->setDefinition(
                [
                    new InputArgument('username', InputArgument::REQUIRED, 'The username'),
                ]
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $username = $input->getArgument('username');

        /** @var User $user */
        if ($user = $em->getRepository(User::class)->findOneByUsername($username)) {
            $user->setEnabled(true);
            $em->flush();

            $output->writeln(sprintf('User <comment>%s</comment> enabled', $username));
        } else {
            $output->writeln(sprintf('<error>User %s was not found</error>', $username));
        }
    }
}
