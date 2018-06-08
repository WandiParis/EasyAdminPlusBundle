<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType\ORM;

use DateTime;

use Symfony\Component\HttpFoundation\Request;

/**
 * DateTimeFilterType
 */
class DateTimeFilterType extends AbstractORMFilterType
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
            $date = empty($data['value']['date']) ? date('d/m/Y') : $data['value']['date'];
            $time = empty($data['value']['time']) ? date('H:i')   : $data['value']['time'];
            $datetime = DateTime::createFromFormat('d/m/Y H:i', $date . ' ' . $time)->format('Y-m-d H:i');

            switch ($data['comparator']) {
                case 'before':
                    $this->queryBuilder->andWhere($this->queryBuilder->expr()->lte($alias . $col, ':var_' . $uniqueId));
                    break;
                case 'after':
                    $this->queryBuilder->andWhere($this->queryBuilder->expr()->gt($alias . $col, ':var_' . $uniqueId));
                    break;
                case 'equals':
                    $datetime = DateTime::createFromFormat('d/m/Y', $date)->format('Y-m-d');
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
        return 'LleEasyAdminPlusBundle:FilterType:dateTimeFilter.html.twig';
    }
}
