<?php

namespace Wandi\EasyAdminPlusBundle\Auth\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Wandi\EasyAdminPlusBundle\Entity\User;
use Wandi\EasyAdminPlusBundle\Auth\Event\EasyAdminPlusAuthEvents;

class UserRemoveRolesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('wandi:easy-admin-plus:user:remove-roles')
            ->setDescription('Remove roles to an admin')
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
        $dispatcher = $container->get('event_dispatcher');

        $username = $input->getArgument('username');
        $roles = $input->getArgument('roles');

        /** @var User $user */
        if (null === ($user = $em->getRepository(User::class)->findOneByUsername($username))) {
            $output->writeln(sprintf('<error>User %s was not found</error>', $username));
            return;
        }

        $dispatcher->dispatch(EasyAdminPlusAuthEvents::USER_PRE_UPDATE_ROLES, new GenericEvent($user));

        if (empty($roles)) {
            $output->writeln(sprintf('<error>No role removed to the user %s</error>', $username));
            return;
        }

        foreach ($roles as $role) {
            $user->removeRole($role);
        }

        $em->flush();

        $dispatcher->dispatch(EasyAdminPlusAuthEvents::USER_POST_UPDATE_ROLES, new GenericEvent($user));

        $output->writeln(sprintf('The role(s) (<comment>%s</comment>) has been removed to the user <comment>%s</comment>', implode('</comment>, <comment>', $roles), $username));
    }
}
