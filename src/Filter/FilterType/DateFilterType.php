<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType;

use DateTime;

use Symfony\Component\HttpFoundation\Request;

/**
 * DateFilterType
 */
class DateFilterType extends AbstractFilterType
{

    protected $yearRange;


    public function configure(array $config = [])
    {
        parent::configure($config);
        $this->yearRange = (isset($config['yearRange']))? $config['yearRange']:null;
        $this->defaults['comparator'] = 'equal';
    }

    public function setDefaults($default){
        parent::setDefaults($default);
        switch($this->defaults['value']){
            case 'now': $return = date('d/m/Y'); break;
            default: $return = $default;
        }
        $this->defaults['value'] = $return;
    }



    /**
     * @param array  $data     The data
     * @param string $this->uniqueId The unique identifier
     */
    public function apply($queryBuilder)
    {
        if (isset($this->data['value']) && $this->data['value'] && isset($this->data['comparator'])) {

            $date = DateTime::createFromFormat('d/m/Y', $this->data['value']);
            if (!$date) {
                return false;
            }

            switch ($this->data['comparator']) {
                case 'equal':
                    $queryBuilder->andWhere($queryBuilder->expr()->like($this->alias.$this->columnName, ':var_' . $this->uniqueId));
                    break;
                case 'before':
                    $queryBuilder->andWhere($queryBuilder->expr()->lte($this->alias.$this->columnName, ':var_' . $this->uniqueId));
                    break;
                case 'after':
                    $queryBuilder->andWhere($queryBuilder->expr()->gt($this->alias.$this->columnName, ':var_' . $this->uniqueId));
                    break;
            }
                $queryBuilder->setParameter('var_' . $this->uniqueId, $date->format('Y-m-d').'%');

        }
    }

    public function getDatePickerOptions(){
        $options = [];
        $options['changeMonth'] =  true;
        $options['changeYear'] = true;
        $options['dateFormat'] = 'dd/mm/yy';
        if($this->yearRange) $options['yearRange'] = $this->yearRange;
        return $options;

    }

    public function getStateTemplate(){
        return '@LleEasyAdminPlus/filter/state/date_filter.html.twig';
    }

    public function getTemplate(){
        return '@LleEasyAdminPlus/filter/type/date_filter.html.twig';
    }
}
