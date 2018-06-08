<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType\ORM;

use Symfony\Component\HttpFoundation\Request;

/**
 * EnumerationFilterType
 */
class NumberRangeFilterType extends AbstractORMFilterType
{
    /**
     * @param Request $request  The request
     * @param array   &$data    The data
     * @param string  $uniqueId The unique identifier
     */
    /*public function bindRequest(Request $request, array &$data, $uniqueId)
    {
        $data['comparator'] = $request->query->get('filter_comparator_' . $uniqueId);
        $data['value'][0]      = $request->query->get('filter_value_' . $uniqueId .'0');
        $data['value'][1]      = $request->query->get('filter_value_' . $uniqueId .'1');
    }*/
  
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
      if (isset($data['value'][0]) or isset($data['value'][1])) {
        if($data['value'][0]){
          $this->queryBuilder->andWhere($alias . $col .' >= :min_'.$uniqueId);
          $this->queryBuilder->setParameter('min_'.$uniqueId, $data['value'][0]);
        }
        if($data['value'][1]) {
          $this->queryBuilder->andWhere($alias . $col .' <= :max_'.$uniqueId);
          $this->queryBuilder->setParameter('max_'.$uniqueId, $data['value'][1]);
        }
      }
      
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return 'LleAdminListBundle:FilterType:numberRangeFilter.html.twig';
    }
}
