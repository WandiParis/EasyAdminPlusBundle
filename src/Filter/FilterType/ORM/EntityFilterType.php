<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType\ORM;

use Symfony\Component\HttpFoundation\Request;
use Lle\EasyAdminPlusBundle\Filter\FilterType\ORM\AbstractORMFilterType;
use Lle\EasyAdminPlusBundle\Filter\HiddenEntity;

class EntityFilterType extends AbstractORMFilterType
{

    protected $table;
    protected $method;
    protected $multiple;
    protected $args;
    protected $group_by;
    protected $method_label;

    /**
     * @param Request $request  The request
     * @param array   &$data    The data
     * @param string  $uniqueId The unique identifier
     */
    public function bindRequest(array &$data, $uniqueId)
    {
        $data['comparator'] = $this->getValueSession('filter_comparator_' . $uniqueId);
        $data['value']      = $this->getValueSession('filter_value_' . $uniqueId);
        return ($data['value'] != '');
    }

     /**
     * @param string $columnName The column name
     * @param string $alias      The alias
     */
    public function __construct($columnName, $config, $alias = 'b')
    {

        parent::__construct($columnName, $config, $alias);
        $this->table = $config['table'];
        $this->method = (isset($config['method']))? $config['method']:'findAll';
        $this->method_label = (isset($config['method_label']))? $config['method_label']:'__toString';
        $this->args = (isset($config['arguments']))? $config['arguments']:null;
        $this->multiple = (isset($config['multiple']))? $config['multiple']:true;
        $this->group_by = (isset($config['group_by']))? $config['group_by']:null;
    }


    /**
     * @param array  $data     The data
     * @param string $uniqueId The unique identifier
     */
    public function apply(array $data, $uniqueId,$alias,$col)
    {
        if (isset($data['value'])) {
            $qb = $this->queryBuilder;
            if($this->getMultiple()){
                $qb->andWhere($qb->expr()->in($alias . $col, ':var_' . $uniqueId));
            }else{
                $qb->andWhere($qb->expr()->eq($alias . $col, ':var_' . $uniqueId));
            }

            $qb->setParameter('var_' . $uniqueId, $data['value']);
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
            $repo = $this->getRepository($this->table);
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
        return 'LleEasyAdminPlusBundle:FilterType:entityFilter.html.twig';
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
