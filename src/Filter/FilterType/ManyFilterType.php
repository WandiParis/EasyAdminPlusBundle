<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType;

use Symfony\Component\HttpFoundation\Request;

/**
 * StringFilterType
 */
class ManyFilterType extends EntityFilterType
{

    protected $join;
    protected $multiple;

     /**
     * @param string $columnName The column name
     * @param string $alias      The alias
     */
    public function __construct($columnName, $config, $alias = 'entity')
    {
        parent::__construct($columnName, $config, $alias);
        $this->join = $config['join'];
        $this->multiple = (isset($config['multiple']))? $config['multiple']:false;
    }


    /**
     * @param array  $data     The data
     * @param string $uniqueId The unique identifier
     */
    public function apply(array $data, $uniqueId,$alias,$col)
    {   
        if (isset($data['value'])) {
            $join =  $alias.$this->join;
            $this->queryBuilder->innerJoin($join,'j');
            $this->queryBuilder->andWhere('j.' . $col.' = :var_' . $uniqueId)->setParameter('var_' . $uniqueId, $data['value']);
        }
    }

    public function getEntities(){
        $em = $this->em; 
        $m = $this->method;
        $elements = $em->getRepository($this->table)->$m();
        return $elements;
    }

    public function getMultiple(){
        return $this->multiple;
    }
}
