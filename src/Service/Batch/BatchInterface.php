<?php

namespace Lle\EasyAdminPlusBundle\Service\Batch;

interface BatchInterface
{

    public function execute($request, array $entityConfig, $ids, $data=[]);

}