<?php
 
 namespace Lle\EasyAdminPlusBundle\Filter;
 
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;

class FilterState
{
    /**
     * @var AttributeBag
     */
    protected $bag;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
        /*try {
            $bag = $this->session->getBag('lle_filter');
        } catch (InvalidArgumentException $e) {
            $bag = new NamespacedAttributeBag('_lle_filter');
            $bag->setName('lle_filter');
            $this->session->registerBag($bag);
        }*/
        $this->bag = null;
    }
    /**
     * @return string
     */
    public function getName()
    {
        return 'session';
    }

    public function reset()
    {
        $this->bag->clear();
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