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
    public function configure(array $config = [])
    {
        parent::configure($config);
        $this->choices = [];
        if(!$this->isAssoc($config['choices'])){
            foreach($config['choices'] as $value){
                $this->choices[$value] = $value;
            }
        }else{
            $this->choices = $config['choices'];
        }
        $this->multiple = (isset($config['multiple']))? $config['multiple']:true;
    }

    public function apply($queryBuilder)
    {   
        if (isset($this->data['value'])) {
            $qb = $queryBuilder;
            if($this->getMultiple()){
                $queryBuilder->andWhere($queryBuilder->expr()->in($this->alias.$this->columnName, ':var_' . $this->uniqueId));
            }else{
                $queryBuilder->andWhere($queryBuilder->expr()->eq($this->alias.$this->columnName, ':var_' . $this->uniqueId));
            }
            $queryBuilder->setParameter('var_' . $this->uniqueId, $this->data['value']);
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


    public function getMultiple(){
        return $this->multiple;
    }

    public function isAssoc(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public function getStateTemplate(){
        return '@LleEasyAdminPlus/filter/state/choice_filter.html.twig';
    }

    public function getTemplate(){
        return '@LleEasyAdminPlus/filter/type/choice_filter.html.twig';
    }
}
