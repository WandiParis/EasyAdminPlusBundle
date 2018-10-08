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

}
