<?php

namespace Wandi\EasyAdminPlusBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Wandi\EasyAdminPlusBundle\Entity\User;

class UserCreateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('wandi:easy-admin:user:create')
            ->setDescription('Create an admin')
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
        $validator = $container->get('validator');

        $username = $input->getArgument('username');
        $password = $input->getArgument('password');

        $user = new User();

        $user->setUsername($username)
            ->setPassword($container->get('security.password_encoder')->encodePassword($user, $password));

        $violations = $validator->validate($user);

        if ($violations->count() === 0) {
            $em->persist($user);
            $em->flush();

            $output->writeln(sprintf('User <comment>%s</comment> created', $username));
        } else {
            foreach ($violations as $violation) {
                /* @var ConstraintViolation $violation */
                $output->writeln(sprintf('<error>%s: %s</error>', ucfirst($violation->getPropertyPath()), mb_strtolower($violation->getMessage())));
            }
        }
    }
}
