<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType;

use App\Entity\Examen;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Workflow\Registry;

/**
 * StringFilterType
 */
class WorkflowFilterType extends ChoiceFilterType
{

    private $choices;
    private $excludes;
    private $multiple;
    private $registry;
    private $em;


    public function __construct(EntityManagerInterface $em, Registry $registry)
    {
        $this->em = $em;
        $this->registry = $registry;
    }

    /**
     * @param string $columnName The column name
     * @param string $alias      The alias
     */
    public function configure(array $config = [])
    {
        $config['choices'] = $config['choices'] ?? $this->registry->get(
                $this->em->getClassMetadata($config['class'] ?? $config['data_class'])->newInstance(),
                $config['name'] ?? null)->getDefinition()->getPlaces();
        parent::configure($config);

        $this->excludes = $config['excludes'] ?? [];
        $this->multiple = $config['multiple'] ?? true;
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

    public function getStateTemplate(){
        return '@LleEasyAdminPlus/filter/state/workflow_filter.html.twig';
    }

    public function getTemplate(){
        return '@LleEasyAdminPlus/filter/type/workflow_filter.html.twig';
    }

}
