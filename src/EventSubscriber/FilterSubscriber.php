<?php

namespace Lle\EasyAdminPlusBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Lle\EasyAdminPlusBundle\Filter\FilterState;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;

class FilterSubscriber implements EventSubscriberInterface
{
    private $session;

    public function __construct( Session $session )
    {
        $this->session = $session;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->isMasterRequest()) {
            $bag = new NamespacedAttributeBag('_admin_filters');
            $bag->setName('admin_filters');    
            $this->session->registerBag($bag);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
           'kernel.request' => ['onKernelRequest', 3024],
        ];
    }
}
