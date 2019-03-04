<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType;

use Symfony\Component\HttpFoundation\Request;
use Lle\EasyAdminPlusBundle\Filter\FilterType\AbstractFilterType;
use Symfony\Component\Routing\RouterInterface;

/**
 * AutoCompleteFilterType
 */
class AutoCompleteFilterType extends AbstractFilterType
{

    protected $url;
    protected $value_filter;
    protected $router;

    public function __construct(RouterInterface $router){
        $this->router = $router;
    }

    public function init($columnName, $label = null, $alias = 'entity')
    {
        parent::init($columnName, $label, $alias);
        $this->data_keys = ['comparator', 'value', 'value_label'];
    }

    public function configure(array $config = [])
    {
        parent::configure($config);
        $this->data_keys = ['comparator', 'value', 'value_label'];
        $this->entity = $config['entity'] ?? null;
        $this->path = $config['path'] ?? null;

        if($this->entity) {
            $path = $this->path;
            $path['route'] = 'easyadmin';
            $path['params'] = ['action' => 'autocomplete', 'entity' => $config['entity']];
            $this->url = $this->router->generate($path['route'], $path['params']);
        }
    }


    /**
     * @param array  $data     The data
     * @param string $uniqueId The unique identifier
     */
    public function apply($queryBuilder)
    {
        if (isset($this->data['value'])) {
            $value = $this->data['value'];
            $qb = $queryBuilder;
            $qb->andWhere($this->alias. $this->columnName .'= :var_' . $this->uniqueId);
            $qb->setParameter('var_' . $this->uniqueId, $value);
        }
    }

    public function getRoute(){
        return $this->route;
    }

    public function getUrl(){
        return $this->url;
    }

    public function getStateTemplate() {
        return '@LleEasyAdminPlus/filter/state/auto_complete_filter.html.twig';
    }

    public function getTemplate(){
        return '@LleEasyAdminPlus/filter/type/auto_complete_filter.html.twig';
    }
}
