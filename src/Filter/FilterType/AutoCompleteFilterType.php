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

     /**
     * @param string $columnName The column name
     * @param string $alias      The alias
     */
    public function __construct($columnName, $label, $config, $alias = 'entity')
    {
        parent::__construct($columnName,$alias);
        $this->route = $config['route'];
    }


    /**
     * @param array  $data     The data
     * @param string $uniqueId The unique identifier
     */
    public function apply(array $data, $uniqueId, $alias, $col)
    {
        if (isset($data['value'])) {
            $value = $data['value']['value'];
            $qb = $this->queryBuilder;
            $qb->andWhere($alias. $col .'= :var_' . $uniqueId);
            $qb->setParameter('var_' . $uniqueId, $value);
        }
    }

    public function getRoute(){
        return $this->route;
    }
}
