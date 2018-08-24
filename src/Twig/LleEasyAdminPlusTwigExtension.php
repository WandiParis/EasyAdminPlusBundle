<?php

namespace Lle\EasyAdminPlusBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class LleEasyAdminPlusTwigExtension extends AbstractExtension
{

    public function getFunctions() 
    {
        return array(
            new TwigFunction('upload_max_filesize', array($this, 'getUploadMaxFilesize')),
        );
    }



    public function getUploadMaxFilesize()
    {
        return ini_get('upload_max_filesize');
    }

    public function getName()
    {
        return 'LleEasyAdminPlus_twig_extension';
    }

}

