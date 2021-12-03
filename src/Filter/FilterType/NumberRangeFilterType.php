<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType;

use Symfony\Component\HttpFoundation\Request;

/**
 * EnumerationFilterType
 */
class NumberRangeFilterType extends AbstractFilterType
{

    public function apply($queryBuilder)
    {
        $data = $this->data;
        if (isset($data['value'][0]) or isset($data['value'][1])) {
            if ($data['value'][0]) {
                $queryBuilder->andWhere($this->alias . $this->columnName . ' >= :min_' . $this->uniqueId);
                $queryBuilder->setParameter('min_' . $this->uniqueId, $data['value'][0]);
            }
            if ($data['value'][1]) {
                $queryBuilder->andWhere($this->alias . $this->columnName . ' <= :max_' . $this->uniqueId);
                $queryBuilder->setParameter('max_' . $this->uniqueId, $data['value'][1]);
            }
        }

    }

    public function getTemplate()
    {
        return '@LleEasyAdminPlus/filter/type/number_range_filter.html.twig';
    }

}
