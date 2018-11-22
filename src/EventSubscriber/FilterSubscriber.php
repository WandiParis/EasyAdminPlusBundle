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
    private $easyadmin_config_manager;
    private $filterState;

    public function __construct( Session $session, FilterState $filterState, $easyadmin_config_manager )
    {
        $this->session = $session;
        $this->easyadmin_config_manager = $easyadmin_config_manager;
        $this->filterState = $filterState;

    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if ($request->attributes->get('_controller') != "Lle\EasyAdminPlusBundle\Controller\AdminController::indexAction") {
            return;
        }
        if (null === $entityName = $event->getRequest()->query->get('entity')) {
            return;
        }

        $entity = $this->easyadmin_config_manager->getEntityConfiguration($entityName);
        if (isset($entity['filter'])) {
            $this->filterState->bindRequest($request, $entity);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
           'kernel.request' => ['onKernelRequest', 0],
        ];
    }
}
