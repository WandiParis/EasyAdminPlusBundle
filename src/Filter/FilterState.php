<?php
 
namespace Lle\EasyAdminPlusBundle\Filter;
 
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManagerInterface;

class FilterState
{

    protected $filters = [];

 /**
     * @var EntityManager
     */
    private $em;
    private $filterChain;

    public function __construct(EntityManagerInterface $em, FilterChain $filterChain)
    {
        $this->em = $em;
        $this->filterChain = $filterChain;
    }

    public function isFilterLink($request) {
        foreach($request->query->all() as $k => $val) {
            if ( strrpos($k, 'filter_') === 0 ) return true;
        }
        return false;
    }

    public function bindRequest($request, $entityConfig) {
        $entity_name = $entityConfig['name'];
        $reset = false;
        $is_link = $this->isFilterLink($request);
        if ( $is_link || ( $request->request->has('reset') && 'reset' === $request->request->get('reset')) ) {
            $data[$entity_name] = [];
            $reset = !$is_link;
        } else {
            $data = $request->getSession()->get('admin_filters');
        }

        foreach ($entityConfig['filter']['fields'] as $filter) {
            $type = $filter['type'] ?? $filter['filter_type'];
            if($this->filterChain->has($type)){
                $filter['config']['class'] = $filter['config']['class'] ?? $entityConfig['class'];
                $filterObj = $this->filterChain->get($type, $filter, $entityConfig);
            }else {
                throw new \Exception($type." not found: Use filter like services tag lle.easy_admin_plus.filter then replace __construct by configure(array \$config = []) you can use:
                \n_instanceof:\n
                    \tLle\EasyAdminPlusBundle\Filter\FilterType\FilterTypeInterface:
                    \t\ttags: [lle.easy_admin_plus.filter]");
            }


            $this->filters[$entity_name][$filter['property']] = $filterObj;
            // set data from sesssion
            $filterObj->setData($data[$entity_name][$filter['property']]??[]);
            // set data from request
            if (!$reset) $filterObj->updateDataFromRequest($request);
            $filterObj->initDefault();

            // save data to session
            $data[$entity_name][$filter['property']] = $filterObj->getData();
        }

        $request->getSession()->set('admin_filters', $data);

    }

    public function getFilters($entity_name) {
        return $this->filters[$entity_name] ?? [];
    }

    public function applyFilters($queryBuilder, $entity_name) {
        foreach($this->filters[$entity_name] as $filter) {
            $filter->addJoin($queryBuilder);
            $filter->apply($queryBuilder);
        }
    }

}