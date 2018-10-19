<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType;

use Symfony\Component\HttpFoundation\Request;
use Lle\EasyAdminPlusBundle\Filter\FilterType\AbstractFilterType;

/**
 * StringFilterType
 */
class UrlAutoCompleteFilterType extends AbstractFilterType
{

    protected $url;
    protected $value_filter;

    public function init($columnName, $label = null, $alias = 'entity')
    {
        parent::init($columnName, $label, $alias);
        $this->data_keys = ['comparator', 'value', 'value_label'];
    }

    public function configure(array $config = [])
    {
        parent::configure($config);
        $this->data_keys = ['comparator', 'value', 'value_label'];
        $this->value_filter = $config['value_filter'];
        $this->url = $config['url'];
    }

    /**
     * @param array  $data     The data
     * @param string $uniqueId The unique identifier
     */
    public function apply($queryBuilder)
    {
        if (isset($this->data['value']) && $this->data['value']) {
            $value = $this->data['value'];
            $queryBuilder->andWhere($this->alias. $this->columnName .'= :var_' . $this->uniqueId);
            $queryBuilder->setParameter('var_' . $this->uniqueId, $value);
        }
    }

    public function getUrl(){
        return $this->url;
    }

    public function getValueFilter(){
        return $this->value_filter;
    }
}
