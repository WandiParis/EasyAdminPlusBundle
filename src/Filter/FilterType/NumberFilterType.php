<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType;

use Symfony\Component\HttpFoundation\Request;

/**
 * NumberFilterType
 */
class NumberFilterType extends AbstractFilterType
{
    public function __construct($columnName, $label, $config = array(), $alias = 'entity')
    {
        parent::__construct($columnName, $label, $config, $alias);
        $this->defaults = [
            'value' => $config['defaultValue'] ?? "",
            'comparator' => $config['defaultComparator'] ?? "eq"
        ];
    }

    public function apply($queryBuilder)
    {
        if (isset($this->data['value'])) {
            switch ($this->data['comparator']) {
                case 'eq':
                    $queryBuilder->andWhere($this->alias . $this->columnName .' = :var_' . $this->uniqueId);
                    $queryBuilder->setParameter('var_' . $this->uniqueId, $this->data['value']);
                    break;
                case 'neq':
                    $queryBuilder->andWhere($queryBuilder->expr()->neq($alias . $this->columnName, ':var_' . $this->uniqueId));
                    break;
                case 'lt':
                    $queryBuilder->andWhere($queryBuilder->expr()->lt($alias . $this->columnName, ':var_' . $this->uniqueId));
                    break;
                case 'lte':
                    $queryBuilder->andWhere($queryBuilder->expr()->lte($alias . $this->columnName, ':var_' . $this->uniqueId));
                    break;
                case 'gt':
                    $queryBuilder->andWhere($this->alias . $this->columnName .' > :var_' . $this->uniqueId);
                    $queryBuilder->setParameter('var_' . $this->uniqueId, '%' . $this->data['value'] . '%');
                    break;
                case 'gte':
                    $queryBuilder->andWhere($this->alias . $this->columnName .' >= :var_' . $this->uniqueId);
                    $queryBuilder->setParameter('var_' . $this->uniqueId, '%' . $this->data['value'] . '%');
                    break;
 
                case 'isnull':
                    $queryBuilder->andWhere($queryBuilder->expr()->isNull($alias . $this->columnName));
                    return;
                case 'isnotnull':
                    $queryBuilder->andWhere($queryBuilder->expr()->isNotNull($alias . $this->columnName));
                    return;
                default:
                    $queryBuilder->andWhere($queryBuilder->expr()->eq($alias . $this->columnName, ':var_' . $this->uniqueId));
                    break;
            }
            $queryBuilder->setParameter('var_' . $this->uniqueId, $this->data['value']);
        }
    }

}
