<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType;

use Symfony\Component\HttpFoundation\Request;

/**
 * BooleanFilterType
 */
class BooleanFilterType extends AbstractFilterType
{
    private $default_value;

    /**
     * @param string $columnName The column name
     * @param string $alias      The alias
     */
    public function __construct($columnName, $label, $config, $alias = 'entity')
    {
        parent::__construct($columnName, $label, $config, $alias);
        $this->default_value = $config['default_value'] ?? null;
    }


    public function apply($queryBuilder)
    {
        $value = $this->data['value'] ?? $this->default_value;
        if (isset($value)) {
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
            return ($this->default_value == $value);
        } else {
            return ($data['value'] == $value);
        }
        return false;
    }

}
