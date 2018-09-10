<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType\ORM;

use Symfony\Component\HttpFoundation\Request;
use Lle\EasyAdminPlusBundle\Filter\FilterType\ORM\AbstractORMFilterType;

/**
 * StringFilterType
 */
class WorkflowFilterType extends ChoiceFilterType
{

    private $choices;
    private $excludes;
    private $multiple;
    /**
     * @param Request $request  The request
     * @param array   &$data    The data
     * @param string  $uniqueId The unique identifier
     */
    public function bindRequest(array &$data, $uniqueId)
    {
        $data['value']      = $this->getValueSession('filter_value_' . $uniqueId);
        return ($data['value'] != null || !empty($this->excludes));
    }

     /**
     * @param string $columnName The column name
     * @param string $alias      The alias
     */
    public function __construct($columnName, $config, $alias = 'b')
    {
        parent::__construct($columnName,$config, $alias);
        $this->choices = $config['choices'];
        $this->excludes = $config['excludes'];
        $this->multiple = (isset($config['multiple']))? $config['multiple']:true;
    }


    /**
     * @param array  $data     The data
     * @param string $uniqueId The unique identifier
     */
    public function apply(array $data, $uniqueId, $alias, $col)
    {   
        if (isset($data['value'])) {
            $qb = $this->queryBuilder;
            if($this->getMultiple()){
                $qb->andWhere($qb->expr()->in($alias.$col, ':var_' . $uniqueId));
            }else{
                $qb->andWhere($qb->expr()->eq($alias.$col, ':var_' . $uniqueId));
            }
            $qb->setParameter('var_' . $uniqueId, $data['value']);
        } elseif (!empty($this->excludes)) {
            $qb = $this->queryBuilder;
            $qb->andWhere($qb->expr()->notin($alias.$col, ':var_' . $uniqueId));
            $qb->setParameter('var_' . $uniqueId, $this->excludes);
            
        }
    }


    public function isSelected($data,$value){
        if(is_null($data['value'])){
            return !in_array($value, $this->excludes);
        }
        if(is_array($data['value'])){
            return in_array($value,$data['value']);
        }else{
            return ($data['value'] == $value);
        }
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return '@LleEasyAdminPlus/FilterType/workflowFilter.html.twig';
    }

}
