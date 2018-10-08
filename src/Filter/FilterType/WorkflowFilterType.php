<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType;

use Symfony\Component\HttpFoundation\Request;

/**
 * StringFilterType
 */
class WorkflowFilterType extends ChoiceFilterType
{

    private $choices;
    private $excludes;
    private $multiple;


     /**
     * @param string $columnName The column name
     * @param string $alias      The alias
     */
    public function __construct($columnName, $label, $config, $alias = 'entity')
    {
        parent::__construct($columnName, $label, $config, $alias);
        $this->choices = $config['choices'];
        $this->excludes = $config['excludes'];
        $this->multiple = (isset($config['multiple']))? $config['multiple']:true;
    }


    public function apply($queryBuilder)
    {   
        if (isset($this->data['value'])) {
            if($this->getMultiple()){
                $queryBuilder->andWhere($queryBuilder->expr()->in($this->alias.$this->columnName, ':var_' . $this->uniqueId));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->eq($this->alias.$this->columnName, ':var_' . $this->uniqueId));
            }
            $queryBuilder->setParameter('var_' . $this->uniqueId, $this->data['value']);
        } elseif (!empty($this->excludes)) {
            $queryBuilder->andWhere($queryBuilder->expr()->notin($this->alias.$this->columnName, ':var_' . $this->uniqueId));
            $queryBuilder->setParameter('var_' . $this->uniqueId, $this->excludes);
            
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
