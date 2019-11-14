<?php

namespace Lle\EasyAdminPlusBundle\Service\Batch;

use Symfony\Component\HttpFoundation\Response;
use Doctrine\Bundle\DoctrineBundle\Registry;

abstract class AbstractBatch implements BatchInterface
{

    public function countSuccess(){
        return null;
    }

}