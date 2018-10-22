<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType;

use Symfony\Component\HttpFoundation\Request;

/**
 * BooleanFilterType
 */
class NotNullFilterType extends AbstractFilterType
{

    /**
     * @param array  $data     The data
     * @param string $uniqueId The unique identifier
     */
    public function apply($queryBuilder)
    {
        if (isset($data['value'])) {
            if ($data['value'] == 'notnull') {
                $queryBuilder->andWhere($this->alias . $this->columnName .'  IS NOT NULL');
            } elseif ($data['value'] == 'null') {
                $queryBuilder->andWhere($this->alias . $this->columnName .'  IS NULL');
            }
        }
    }

    public function getTemplate(){
        return '@LleEasyAdminPlus/filter/type/not_null_filter.html.twig';
    }

}
