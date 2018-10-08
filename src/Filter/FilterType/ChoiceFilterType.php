<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType;

use Symfony\Component\HttpFoundation\Request;
use Lle\EasyAdminPlusBundle\Filter\FilterType\AbstractFilterType;

/**
 * StringFilterType
 */
class ChoiceFilterType extends AbstractFilterType
{

    private $choices;
    private $multiple;


     /**
     * @param string $columnName The column name
     * @param string $alias      The alias
     */
    public function __construct($columnName, $label, $config, $alias = 'entity')
    {
        parent::__construct($columnName, $label, $config, $alias);
        $this->choices = $config['choices'];
        $this->multiple = (isset($config['multiple']))? $config['multiple']:true;
    }

    public function apply($queryBuilder)
    {   
        if (isset($this->data['value'])) {
            $qb = $queryBuilder;
            if($this->getMultiple()){
                $queryBuilder->andWhere($queryBuilder->expr()->in($alias.$col, ':var_' . $uniqueId));
            }else{
                $queryBuilder->andWhere($queryBuilder->expr()->eq($alias.$col, ':var_' . $uniqueId));
            }
            $queryBuilder->setParameter('var_' . $uniqueId, $data['value']);
        }
    }

    public function getChoices(){
        return $this->choices;
    }

    public function isSelected($data,$value){

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
        return '@LleEasyAdminPlus/FilterType/choiceFilter.html.twig';
    }

    public function getMultiple(){
        return $this->multiple;
    }
}
