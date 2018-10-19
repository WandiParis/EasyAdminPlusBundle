<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType;

use DateTime;

use Symfony\Component\HttpFoundation\Request;

/**
 * DateTimeFilterType
 */
class DateTimeFilterType extends AbstractFilterType
{

    /**
     * @param array  $data     The data
     * @param string $this->uniqueId The unique identifier
     */
    public function apply($queryBuilder)
    {
        if (isset($data['value']) && isset($data['comparator'])) {
            /** @var DateTime $datetime */
            [$date, $time] = [null,null];
            if(strstr($data['value']['date'], ' ')) {
                [$date, $time] = explode(' ', $data['value']['date']);
            }
            $date = empty($date) ? date('d/m/Y') : $date;
            $time = empty($time) ? date('H:i')   : $time;
            $datetime = DateTime::createFromFormat('d/m/Y H:i', $date . ' ' . $time);
            switch ($data['comparator']) {
                case 'before':
                    $this->queryBuilder->andWhere($queryBuilder->expr()->lte($alias . $col, ':var_' . $this->uniqueId));
                    break;
                case 'after':
                    $this->queryBuilder->andWhere($queryBuilder->expr()->gt($alias . $col, ':var_' . $this->uniqueId));
                    break;
                case 'equal':
                    $this->queryBuilder->andWhere($alias . $this->columnName.' = :var_' . $this->uniqueId);
                    break;
            }
            $this->queryBuilder->setParameter('var_' . $this->uniqueId, $datetime);
        }
    }

}
