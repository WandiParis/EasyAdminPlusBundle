<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType\ORM;

use Symfony\Component\HttpFoundation\Request;

/**
 * StringFilterType
 */
class ExactStringFilterType extends AbstractORMFilterType
{
    /**
     * @param Request $request  The request
     * @param array   &$data    The data
     * @param string  $uniqueId The unique identifier
     */
    public function bindRequest(array &$data, $uniqueId)
    {
        $data['comparator'] = $this->getDefaultComparator();
        if($this->getValueSession('filter_comparator_' . $uniqueId) == 'isnull') {

            $data['comparator'] = $this->getValueSession('filter_comparator_' . $uniqueId);
            return true;
        }

        if ($this->getValueSession('filter_value_' . $uniqueId)) {
            $data['comparator'] = $this->getValueSession('filter_comparator_' . $uniqueId);
            $data['value']      = $this->getValueSession('filter_value_' . $uniqueId);
            return ($data['value'] != null);
        } else return false;
    }

    /**
     * @param array  $data     The data
     * @param string $uniqueId The unique identifier
     */
    public function apply(array $data, $uniqueId, $alias, $col)
    {
        if (!array_key_exists('value', $data) || !isset($data['value']) ) {
            $data['value'] = $this->defaultValue;
        }
        if(isset($data['comparator'])) {
            if( $data['comparator'] == 'isnull') {
                $this->queryBuilder->andWhere($alias . $col .' IS NULL or '.$alias . $col ." = '' ");
            }
            elseif( $data['comparator'] == 'isnotnull') {
                $this->queryBuilder->andWhere($alias . $col .' IS NOT NULL AND '.$alias . $col ." <> '' ");
                die ("here");
            }
            elseif (isset($data['value']) ) {
                switch ($data['comparator']) {
                    case 'equals':
                        $this->queryBuilder->andWhere($alias . $col .' = :var_' . $uniqueId);
                        $this->queryBuilder->setParameter('var_' . $uniqueId, $data['value']);
                        break;
                    case 'notequals':
                        $this->queryBuilder->andWhere($alias . $col .' != :var_' . $uniqueId);
                        $this->queryBuilder->setParameter('var_' . $uniqueId, $data['value']);
                        break;
                }

            }
        }
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return 'LleAdminListBundle:FilterType:exactStringFilter.html.twig';
    }

    /**
     * @param string $columnName The column name
     * @param string $alias      The alias
     */
    public function __construct($columnName, $config = array(), $alias = 'b')
    {
        parent::__construct($columnName, $config, $alias);
        $this->defaultValue = (isset($config['defaultValue']))? $config['defaultValue']:"";
        $this->defaultComparator = (isset($config['defaultComparator']))? $config['defaultComparator']:"equals";
    }

    public function getDefaultComparator() {
        return $this->defaultComparator;
    }
}
