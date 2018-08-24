<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType\ORM;

use Symfony\Component\HttpFoundation\Request;
use Lle\EasyAdminPlusBundle\Filter\FilterType\ORM\AbstractORMFilterType;

/**
 * StringFilterType
 */
class UrlAutoCompleteFilterType extends AbstractORMFilterType
{

    protected $url;
    protected $value_filter;
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
        $this->value_filter = $config['value_filter'];
        $this->url = $config['url'];
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
        return 'LleEasyAdminPlusBundle:FilterType:urlAutoCompleteFilter.html.twig';
    }

    public function getUrl(){
        return $this->url;
    }

    public function getValueFilter(){
        return $this->value_filter;
    }
}
