<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType\ORM;

use Symfony\Component\HttpFoundation\Request;

/**
 * StringFilterType
 */
class StringFilterType extends AbstractORMFilterType
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
                    case 'contains':
                        $this->queryBuilder->andWhere($alias . $col .' LIKE :var_' . $uniqueId);
                        $this->queryBuilder->setParameter('var_' . $uniqueId, '%' . $data['value'] . '%');
                        break;
                    case 'doesnotcontain':
                        $this->queryBuilder->andWhere($alias . $col .' NOT LIKE :var_' . $uniqueId);
                        $this->queryBuilder->setParameter('var_' . $uniqueId, '%' . $data['value'] . '%');
                        break;
                    case 'startswith':
                        $this->queryBuilder->andWhere($alias . $col .' LIKE :var_' . $uniqueId);
                        $this->queryBuilder->setParameter('var_' . $uniqueId, $data['value'] . '%');
                        break;
                    case 'endswith':
                        $this->queryBuilder->andWhere($alias . $col .' LIKE :var_' . $uniqueId);
                        $this->queryBuilder->setParameter('var_' . $uniqueId, '%' . $data['value']);
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
        return 'LleEasyAdminPlusBundle:FilterType:stringFilter.html.twig';
    }

    /**
     * @param string $columnName The column name
     * @param string $alias      The alias
     */
    public function __construct($columnName, $config = array(), $alias = 'b')
    {
        parent::__construct($columnName, $config, $alias);
        $this->defaultValue = (isset($config['defaultValue']))? $config['defaultValue']:"";
        $this->defaultComparator = (isset($config['defaultComparator']))? $config['defaultComparator']:"startswith";
    }

    public function getDefaultComparator() {
        return $this->defaultComparator;
    }
}
