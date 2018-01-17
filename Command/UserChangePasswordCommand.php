<?php

namespace Wandi\EasyAdminPlusBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Wandi\EasyAdminPlusBundle\Entity\User;

class UserChangePasswordCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('wandi:easy-admin:user:change-password')
            ->setDescription('Change admin password')
            ->setDefinition(
                [
                    new InputArgument('username', InputArgument::REQUIRED, 'The username'),
                    new InputArgument('password', InputArgument::REQUIRED, 'The password'),
                ]
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $username = $input->getArgument('username');
        $password = $input->getArgument('password');

        /** @var User $user */
        if($user = $em->getRepository(User::class)->findOneByUsername($username)){
            $user->setPassword($container->get('security.password_encoder')->encodePassword($user, $password));

            $em->flush();

            $output->writeln(sprintf('User <comment>%s</comment> password changed', $username));
        } else{
            $output->writeln(sprintf('<error>User %s was not found</error>', $username));
        }
    }
}
