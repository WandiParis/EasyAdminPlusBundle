<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType;

use Symfony\Component\HttpFoundation\Request;
use Lle\EasyAdminPlusBundle\Filter\FilterType\AbstractFilterType;

/**
 * StringFilterType
 */
class AutoCompleteFilterType extends AbstractFilterType
{

    protected $route;

    public function configure(array $config = [])
    {
        parent::configure($config);
        $this->route = $config['route'];
    }


    /**
     * @param array  $data     The data
     * @param string $uniqueId The unique identifier
     */
    public function apply($queryBuilder)
    {
        if (isset($data['value'])) {
            $value = $data['value']['value'];
            $qb = $queryBuilder;
            $qb->andWhere($this->alias. $this->columnName .'= :var_' . $this->uniqueId);
            $qb->setParameter('var_' . $this->uniqueId, $value);
        }
    }

    public function getRoute(){
        return $this->route;
    }

    public function getStateTemplate(){
        return '@LleEasyAdminPlus/filter/state/auto_complete_filter.html.twig';
    }

    public function getTemplate(){
        return '@LleEasyAdminPlus/filter/type/auto_complete_filter.html.twig';
    }
}
