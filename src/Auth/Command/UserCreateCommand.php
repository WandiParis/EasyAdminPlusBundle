<?php

namespace Wandi\EasyAdminPlusBundle\Auth\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Validator\ConstraintViolation;
use Wandi\EasyAdminPlusBundle\Entity\User;
use Wandi\EasyAdminPlusBundle\Auth\Event\EasyAdminPlusAuthEvents;

class UserCreateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('wandi:easy-admin-plus:user:create')
            ->setDescription('Create an admin')
            ->setDefinition(
                [
                    new InputArgument('username', InputArgument::REQUIRED, 'The username'),
                    new InputArgument('password', InputArgument::REQUIRED, 'The password'),
                    new InputArgument('roles', InputArgument::IS_ARRAY, 'The roles'),
                ]
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $validator = $container->get('validator');
        $dispatcher = $container->get('event_dispatcher');

        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $roles = $input->getArgument('roles');

        $dispatcher->dispatch(EasyAdminPlusAuthEvents::USER_PRE_CREATE);

        $user = new User();
        $user->setUsername($username)
            ->setPassword($container->get('security.password_encoder')->encodePassword($user, $password));

        foreach ($roles as $role) {
            $user->addRole($role);
        }

        $violations = $validator->validate($user);

        if ($violations->count()) {
            foreach ($violations as $violation) {
                /* @var ConstraintViolation $violation */
                $output->writeln(sprintf('<error>%s: %s</error>', ucfirst($violation->getPropertyPath()), mb_strtolower($violation->getMessage())));
            }
            return;
        }

        $em->persist($user);
        $em->flush();

        $dispatcher->dispatch(EasyAdminPlusAuthEvents::USER_POST_CREATE, new GenericEvent($user));

        $output->writeln(sprintf('User <comment>%s</comment> created', $username));
    }
}
