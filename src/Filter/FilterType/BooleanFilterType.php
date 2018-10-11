<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType;

use Symfony\Component\HttpFoundation\Request;

/**
 * BooleanFilterType
 */
class BooleanFilterType extends AbstractFilterType
{

    /**
     * @param string $columnName The column name
     * @param string $alias      The alias
     */
    public function __construct($columnName, $label, $config, $alias = 'entity')
    {
        parent::__construct($columnName, $label, $config, $alias);
        $this->defaults['value'] = $config['default_value'] ?? 'all';
    }


    public function apply($queryBuilder)
    {
        $value = $this->data['value'];
        if (isset($value) && $value != 'all') {
            switch ($value) {
                case 'true':
                    $queryBuilder->andWhere($queryBuilder->expr()->eq($this->alias . $this->columnName, 'true'));
                    break;
                case 'false':
                    $queryBuilder->andWhere($queryBuilder->expr()->eq($this->alias . $this->columnName, 'false'));
                    break;
            }
        }
    }

    public function isSelected($data,$value){
        if(! isset($data['value'])){
            return ($this->defaults['value'] == $value);
        } else {
            return ($data['value'] == $value);
        }
        return false;
    }

}
