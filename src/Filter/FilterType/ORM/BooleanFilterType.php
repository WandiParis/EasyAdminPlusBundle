<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType\ORM;

use Symfony\Component\HttpFoundation\Request;

/**
 * BooleanFilterType
 */
class BooleanFilterType extends AbstractORMFilterType
{
    private $default_value;
    /**
     * @param Request $request  The request
     * @param array   &$data    The data
     * @param string  $uniqueId The unique identifier
     */
    public function bindRequest(array &$data, $uniqueId)
    {
        $data['value'] = $this->getValueSession('filter_value_' . $uniqueId);
        return ($data['value'] != '');
    }

    /**
     * @param string $columnName The column name
     * @param string $alias      The alias
     */
    public function __construct($columnName, $config, $alias = 'b')
    {
        parent::__construct($columnName,$config, $alias);
        $this->default_value = $config['default_value'] ?? null;
    }
    /**
     * @param array  $data     The data
     * @param string $uniqueId The unique identifier
     */
    public function apply(array $data, $uniqueId, $alias, $col)
    {
        $value = $data['value'] ?? $this->default_value;
        if (isset($value)) {
            switch ($value) {
                case 'true':
                    $this->queryBuilder->andWhere($this->queryBuilder->expr()->eq($alias . $col, 'true'));
                    break;
                case 'false':
                    $this->queryBuilder->andWhere($this->queryBuilder->expr()->eq($alias . $col, 'false'));
                    break;
            }
        }
    }

    public function isSelected($data,$value){
        if(! isset($data['value'])){
            return ($this->default_value == $value);
        } else {
            return ($data['value'] == $value);
        }
        return false;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return '@LleEasyAdminPlus/FilterType/booleanFilter.html.twig';
    }
}
