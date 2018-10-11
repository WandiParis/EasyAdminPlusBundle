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

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function bindRequest($request, $entity_conf) {
        
        $entity_name = $entity_conf['name'];
        $reset = false;
        if ($request->request->has('reset') && 'reset' === $request->request->get('reset')) {
            $data[$entity_name] = [];
            $reset = true;
        } else {
            $data = $request->getSession()->get('admin_filters');
        }

        foreach ($entity_conf['filter']['fields'] as $filter) {
            $reflection_class = new \ReflectionClass($filter['filter_type']);
            $filterObj = $reflection_class->newInstanceArgs([ 
                $filter['property'], $filter['label'], $filter['config']
            ]);
            $filterObj->setEm($this->em);

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