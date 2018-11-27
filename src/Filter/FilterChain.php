<?php
namespace Lle\EasyAdminPlusBundle\Filter;

use Lle\EasyAdminPlusBundle\Filter\FilterType\FilterTypeInterface;

class FilterChain
{

    private $filters = [];

    public function __construct(iterable $filters)
    {
        foreach($filters as $filter){
            $this->filters[get_class($filter)] = $filter;
        }
    }

    public function addFilter(FilterTypeInterface $filter)
    {
        $this->filters[get_class($filter)] = $filter;
    }

    public function getfilters():iterable{
        return $this->filters;
    }

    public function get($filterName, $filterConfig): FilterTypeInterface{
        $filter = clone $this->filters[$filterName];
        $filter->init($filterConfig['property'], $filterConfig['label'] ?? null);
        $filter->configure($filterConfig['config']);
        return $filter;
    }

    public function has($filterName): bool{
        return isset($this->filters[$filterName]);
    }
}