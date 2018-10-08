<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType;

use Symfony\Component\HttpFoundation\Request;
use Lle\EasyAdminPlusBundle\Filter\FilterType\AbstractFilterType;
use Lle\EasyAdminPlusBundle\Filter\FilterType\HiddenEntity;

class EntityFilterType extends AbstractFilterType
{

    protected $table;
    protected $method;
    protected $multiple;
    protected $args;
    protected $group_by;
    protected $method_label;

    public function __construct($columnName, $label, $config, $alias = 'entity')
    {

        parent::__construct($columnName, $label, $config, $alias);
        $this->table = $config['table'];
        $this->method = (isset($config['method']))? $config['method']:'findAll';
        $this->method_label = (isset($config['method_label']))? $config['method_label']:'__toString';
        $this->args = (isset($config['arguments']))? $config['arguments']:null;
        $this->multiple = (isset($config['multiple']))? $config['multiple']:true;
        $this->group_by = (isset($config['group_by']))? $config['group_by']:null;
    }

    public function apply($queryBuilder)
    {
        if (isset($this->data['value'])) {
            if ($this->getMultiple()) {
                $queryBuilder->andWhere($queryBuilder->expr()->in($this->alias .$this->columnName, ':var_' . $this->uniqueId));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->eq($this->alias .$this->columnName, ':var_' . $this->uniqueId));
            }

            $queryBuilder->setParameter('var_' . $this->uniqueId, $this->data['value']);
        }
    }



    public function getEntities($data){
        if($this->isHidden()){ //si le filtre est hidden pas de requet. héééé ouai
            $elements = array();
            if(is_array($data['value'])){
                foreach($data['value'] as $value) $elements[] = new HiddenEntity($value);
            }else{
                $elements[] = new HiddenEntity($data['value']);
            }
        }else{
            $m = $this->method;
            $args = $this->args;
            $repo = $this->em->getRepository($this->table);
            if($args){
                $classRfx = new \ReflectionClass(get_class($repo));
                $methodRfx = $classRfx->getMethod($m);
                $elements = $methodRfx->invokeArgs($repo,$args);
            }else{
                $elements = $repo->$m();
            }
        }
        return $elements;
    }

    public function isSelected($data,$entity){
        if($this->getMultiple() and is_array($data['value'])){
            return in_array($entity->getId(),$data['value']);
        }else{
            return ($data && $data['value'] == $entity->getId());
        }
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return '@LleEasyAdminPlus/FilterType/entityFilter.html.twig';
    }

    public function getMultiple(){
        return $this->multiple;
    }

    public function getGroupBy(){
        return $this->group_by;
    }

    public function getLabelForOptGroup($entity) {

        $group_by = $this->getGroupBy();
        $method = "get".ucfirst($group_by);

        return call_user_func(array($entity, $method));
    }

    public function getLabel($entity) {

        return call_user_func(array($entity, $this->method_label));
    }
}
