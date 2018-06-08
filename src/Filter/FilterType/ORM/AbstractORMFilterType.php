<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType\ORM;

use Lle\EasyAdminPlusBundle\Filter\FilterType\AbstractFilterType;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Lle\EasyAdminPlusBundle\Lib\QueryHelper;

/**
 * The abstract filter used for ORM query builder
 */
abstract class AbstractORMFilterType extends AbstractFilterType
{
    /**
     * @var QueryBuilder $queryBuilder
     */
    protected $queryBuilder;

    protected $em = null;


    /**
     * @param QueryBuilder $queryBuilder
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function setEm(EntityManager $em){
        $this->em = $em;
    }

    public function getRepository($name){
        return $this->em->getRepository($name);
    }

    public function getColumnName(){
        return $this->columnName;
    }

    public function init(){
        $queryHelper = new QueryHelper();
        $path = $queryHelper->getPath($this->queryBuilder,str_replace('.','',$this->getAlias()),$this->columnName);
        //echo $this->getAlias().':'.$this->columnName.'  --> '.$path['alias'].$path['column'].'<br/>';
        return $path;
    }




}
