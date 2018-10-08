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
     * @param Request $request  The request
     * @param array   &$data    The data
     * @param string  $uniqueId The unique identifier
     */
    public function bindRequest(array &$data, $uniqueId)
    {
        $data['comparator'] = $this->getValueSession('filter_comparator_' . $uniqueId);
        $data['value']      = $this->getValueSession('filter_value_' . $uniqueId);
        return ($data['value'] != null);
    }

    /**
     * @param array  $data     The data
     * @param string $uniqueId The unique identifier
     */
    public function apply(array $data, $uniqueId,$alias,$col)
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
                    $this->queryBuilder->andWhere($this->queryBuilder->expr()->lte($alias . $col, ':var_' . $uniqueId));
                    break;
                case 'after':
                    $this->queryBuilder->andWhere($this->queryBuilder->expr()->gt($alias . $col, ':var_' . $uniqueId));
                    break;
                case 'equal':
                    $this->queryBuilder->andWhere($alias . $col.' = :var_' . $uniqueId);
                    break;
            }
            $this->queryBuilder->setParameter('var_' . $uniqueId, $datetime);
        }
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return '@LleEasyAdminPlus/FilterType/dateTimeFilter.html.twig';
    }
}
