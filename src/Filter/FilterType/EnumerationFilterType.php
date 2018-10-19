<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType;

use Symfony\Component\HttpFoundation\Request;

/**
 * EnumerationFilterType
 */
class EnumerationFilterType extends AbstractFilterType
{

    public function apply($queryBuilder)
    {
        if (isset($data['value']) && isset($data['comparator'])) {
            switch ($data['comparator']) {
                case 'in':
                    $queryBuilder->andWhere($queryBuilder->expr()->in($alias . $this->columnName, ':var_' . $this->uniqueId));
                    $queryBuilder->setParameter('var_' . $uniqueId, $data['value'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
                    break;
                case 'notin':
                    $queryBuilder->andWhere($queryBuilder->expr()->notIn($alias . $this->columnName, ':var_' . $this->uniqueId));
                    $queryBuilder->setParameter('var_' . $this->uniqueId, $data['value'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
                    break;
            }
        }
    }

}
