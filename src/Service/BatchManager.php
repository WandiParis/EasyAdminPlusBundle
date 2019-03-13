<?php

namespace Lle\EasyAdminPlusBundle\Service;

use Lle\EasyAdminPlusBundle\Service\Batch\BatchInterface;
use Lle\EasyAdminPlusBundle\Service\Exporter\ExporterInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Component\Translation\TranslatorInterface;

class BatchManager
{

    private $batchs = [];


    public function __construct(iterable $batchs)
    {
        foreach($batchs as $batch){
            if($batch instanceof BatchInterface) {
                $this->batchs[get_class($batch)] = $batch;
            }
        }
    }

    public function getBatch($classname): BatchInterface{
        if(array_key_exists($classname, $this->batchs)){
            return $this->batchs[$classname];
        }else{
            throw new \Exception('The batch '. $classname .' is not found');
        }
    }


}