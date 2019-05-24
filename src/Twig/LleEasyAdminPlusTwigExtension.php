<?php

namespace Lle\EasyAdminPlusBundle\Twig;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigManager;
use EasyCorp\Bundle\EasyAdminBundle\Router\EasyAdminRouter;
use Lle\EasyAdminPlusBundle\Service\EditInPlaceFactory;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class LleEasyAdminPlusTwigExtension extends AbstractExtension
{

    private $propertyAccessor;
    private $eipFactory;

    public function __construct(PropertyAccessor $propertyAccessor, EditInPlaceFactory $eipFactory)
    {
        $this->propertyAccessor = $propertyAccessor;
        $this->eipFactory = $eipFactory;
    }

    public function getFunctions() 
    {
        return array(
            new TwigFunction('upload_max_filesize', array($this, 'getUploadMaxFilesize')),
            new TwigFunction('easyadmin_render_raw_value', array($this, 'renderRawValue')),
            new TwigFunction('easyadmin_get_eip_type', array($this, 'getEipType')),
        );
    }



    public function getUploadMaxFilesize()
    {
        return ini_get('upload_max_filesize');
    }

    public function renderRawValue($item, $fieldName)
    {
        return $this->propertyAccessor->getValue($item, $fieldName);
    }
    
    public function getEipType($fieldMetadata, $value){
        return $this->eipFactory->getEditInPlaceType($fieldMetadata['type']);
    }

    public function getName()
    {
        return 'LleEasyAdminPlus_twig_extension';
    }

}

