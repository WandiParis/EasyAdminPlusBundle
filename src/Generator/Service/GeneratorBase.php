<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class GeneratorBase
{
    protected $parameters;
    protected $em;
    protected $projectDir;
    protected $container;

    /**
     * GeneratorBase constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->parameters = $container->getParameter('easy_admin_plus')['generator'];
        $this->projectDir = $container->getParameter('kernel.project_dir');
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
