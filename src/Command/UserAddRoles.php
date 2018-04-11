<?php

namespace Wandi\EasyAdminPlusBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wandi\EasyAdminPlusBundle\Entity\User;

class UserAddRoles extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('wandi:easy-admin-plus:user:add-roles')
            ->setDescription('Add roles to an admin')
            ->setDefinition(
                [
                    new InputArgument('username', InputArgument::REQUIRED, 'The username'),
                    new InputArgument('roles', InputArgument::IS_ARRAY, 'The roles'),
                ]
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $username = $input->getArgument('username');
        $roles = $input->getArgument('roles');

        /** @var User $user */
        if (null === ($user = $em->getRepository(User::class)->findOneByUsername($username))) {
            $output->writeln(sprintf('<error>User %s was not found</error>', $username));

            return;
        }

        if (empty($roles)) {
            $output->writeln(sprintf('<error>No role added to the user %s</error>', $username));

            return;
        }

        foreach ($roles as $role) {
            $user->addRole($role);
        }
        $em->flush();

        $output->writeln(sprintf('The role(s) (<comment>%s</comment>) has been added to the user <comment>%s</comment>', implode('</comment>, <comment>', $roles), $username));
    }
}
