<?php

namespace Wandi\EasyAdminPlusBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wandi\EasyAdminPlusBundle\Entity\User;

class UserDisableCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('wandi:easy-admin-plus:user:disable')
            ->setDescription('Disable an admin')
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
            $user->setEnabled(false);

            $em->flush();

            $output->writeln(sprintf('User <comment>%s</comment> disabled', $username));
        } else {
            $output->writeln(sprintf('<error>User %s was not found</error>', $username));
        }
    }
}
