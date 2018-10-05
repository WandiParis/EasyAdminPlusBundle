<?php
 
 namespace Lle\EasyAdminPlusBundle\Filter;
 
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;

class FilterState
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'session';
    }


    public function bindRequest($request, $entity_conf) {

        $entity = $request->get('entity', null);
        if ($request->request->has('reset') && $request->request->get('reset') === 'reset') {
            print_r($this->bag->get($prefix));
        }
        foreach($entity_conf['filter']['fields'] as $filter) {
            print $filter['property'];
        }
        die('');
        // get filter has priority
        $has_get = false;
            $new_get_value = $request->query->get($id, null);
            if ($new_get_value) {
                if ( !$has_get ) {
                    $this->clear($prefix);
                    $has_get = true;
                }
                $this->bag->set($prefix.'/'.$new_get_value); 
            }
        
    }
    public function getValueSession($id)
    {
        $gid = $this->request->get('entity', null).$id;
        $session = $this->request->getSession();
        if ($this->request->request->has('reset') && $this->request->request->get('reset') === 'reset') {
            $session->remove($gid);
            return null;
        }
        $new_val_post = $this->request->request->get($id, null);
        $new_val_get = $this->request->query->get($id, null);
        $new_val = $new_val_post ?? $new_val_get;
        if ($new_val) {
            $session->set($gid, $new_val);
            return $new_val;
        } else {
            if (!($this->request->request->has('filter') && $this->request->request->get('filter') === 'filter')) {
                return $session->get($gid, null);
            } else {
                $session->remove($gid);
                return null;
            }
        }

    }    
}