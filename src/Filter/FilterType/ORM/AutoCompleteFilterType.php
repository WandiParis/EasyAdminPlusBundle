<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType\ORM;

use Symfony\Component\HttpFoundation\Request;
use Lle\EasyAdminPlusBundle\Filter\FilterType\ORM\AbstractORMFilterType;

/**
 * StringFilterType
 */
class AutoCompleteFilterType extends AbstractORMFilterType
{

    protected $route;
    /**
     * @param Request $request  The request
     * @param array   &$data    The data
     * @param string  $uniqueId The unique identifier
     */
    public function bindRequest(array &$data, $uniqueId)
    {
        $data['comparator'] = $this->getValueSession('filter_comparator_' . $uniqueId);
        $data['value'] = array();
        $data['value']['value']      = $this->getValueSession('filter_value_' . $uniqueId);
        $data['value']['label']      = $this->getValueSession('filter_value_' . $uniqueId.'_label');
        return ($data['value']['value'] != '');
    }

     /**
     * @param string $columnName The column name
     * @param string $alias      The alias
     */
    public function __construct($columnName,$config,$alias = 'b')
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


    /**
     * @return string
     */
    public function getTemplate()
    {
        return 'LleAdminListBundle:FilterType:autoCompleteFilter.html.twig';
    }

    public function getRoute(){
        return $this->route;
    }
}
