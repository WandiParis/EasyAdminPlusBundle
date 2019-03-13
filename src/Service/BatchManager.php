<?php

namespace Lle\EasyAdminPlusBundle\Service;

use Lle\EasyAdminPlusBundle\Service\Batch\BatchInterface;

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

    public function getBatch(string $classname): BatchInterface{
        if(array_key_exists($classname, $this->batchs)){
            return $this->batchs[$classname];
        }else{
            throw new \Exception('The batch '. $classname .' is not found');
        }
    }


}