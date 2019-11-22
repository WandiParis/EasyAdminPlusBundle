<?php

namespace Wandi\EasyAdminPlusBundle\Generator\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

abstract class GeneratorBase
{
    /** @var ParameterBagInterface $parameterBag */
    protected $parameterBag;
    /** @var EntityManagerInterface $em */
    protected $em;
    protected $projectDir;
    protected $generatorParameters;


    public function __construct(EntityManagerInterface $em, ParameterBagInterface $parameterBag)
    {
        $this->em = $em;
        $this->parameterBag = $parameterBag;
        $this->generatorParameters = $parameterBag->get('easy_admin_plus')['generator'];
        $this->projectDir = $parameterBag->get('kernel.project_dir');
    }
}
