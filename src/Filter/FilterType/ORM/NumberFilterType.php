<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType\ORM;

use Symfony\Component\HttpFoundation\Request;

/**
 * NumberFilterType
 */
class NumberFilterType extends AbstractORMFilterType
{
    /**
     * @param Request $request  The request
     * @param array   &$data    The data
     * @param string  $uniqueId The unique identifier
     */
    public function bindRequest(array &$data, $uniqueId)
    {
        $data['comparator'] = $this->getValueSession('filter_comparator_' . $uniqueId);
        $data['value']      = $this->getValueSession('filter_value_' . $uniqueId);
        return ($data['value'] != '');
    }

    /**
     * @param array  $data     The data
     * @param string $uniqueId The unique identifier
     */
    public function apply(array $data, $uniqueId,$alias,$col)
    {
        if (isset($data['value'])) {
            switch ($data['comparator']) {
                case 'eq':
                    $this->queryBuilder->andWhere($alias . $col .' = :var_' . $uniqueId);
                    $this->queryBuilder->setParameter('var_' . $uniqueId, $data['value']);
                    break;
                case 'neq':
                    $this->queryBuilder->andWhere($this->queryBuilder->expr()->neq($alias . $col, ':var_' . $uniqueId));
                    break;
                case 'lt':
                    $this->queryBuilder->andWhere($this->queryBuilder->expr()->lt($alias . $col, ':var_' . $uniqueId));
                    break;
                case 'lte':
                    $this->queryBuilder->andWhere($this->queryBuilder->expr()->lte($alias . $col, ':var_' . $uniqueId));
                    break;
                case 'gt':
                    $this->queryBuilder->andWhere($alias . $col .' > :var_' . $uniqueId);
                    $this->queryBuilder->setParameter('var_' . $uniqueId, '%' . $data['value'] . '%');
                    break;
                case 'gte':
                    $this->queryBuilder->andWhere($alias . $col .' >= :var_' . $uniqueId);
                    $this->queryBuilder->setParameter('var_' . $uniqueId, '%' . $data['value'] . '%');
                    break;
 
                case 'isnull':
                    $this->queryBuilder->andWhere($this->queryBuilder->expr()->isNull($alias . $col));
                    return;
                case 'isnotnull':
                    $this->queryBuilder->andWhere($this->queryBuilder->expr()->isNotNull($alias . $col));
                    return;
                default:
                    $this->queryBuilder->andWhere($this->queryBuilder->expr()->eq($alias . $col, ':var_' . $uniqueId));
                    break;
            }
            $this->queryBuilder->setParameter('var_' . $uniqueId, $data['value']);
        }
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return 'LleAdminListBundle:FilterType:numberFilter.html.twig';
    }
}
