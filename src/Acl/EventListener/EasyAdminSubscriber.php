<?php

namespace Wandi\EasyAdminPlusBundle\Acl\EventListener;

use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Wandi\EasyAdminPlusBundle\Acl\Security\AdminAuthorizationChecker;
use Wandi\EasyAdminPlusBundle\Exporter\Event\EasyAdminPlusExporterEvents;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    private $adminAuthorizationChecker;

    public function __construct(AdminAuthorizationChecker $adminAuthorizationChecker)
    {
        $this->adminAuthorizationChecker = $adminAuthorizationChecker;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return array(
            EasyAdminEvents::PRE_NEW => 'checkUserRights',
            EasyAdminEvents::PRE_LIST => 'checkUserRights',
            EasyAdminEvents::PRE_SEARCH => 'checkUserRights',
            EasyAdminEvents::PRE_EDIT => 'checkUserRights',
            EasyAdminEvents::PRE_SHOW => 'checkUserRights',
            EasyAdminEvents::PRE_DELETE => 'checkUserRights',
            EasyAdminPlusExporterEvents::PRE_EXPORT => 'checkUserRights',
        );
    }

    /**
     * Throws an AccessDeniedException if user haven't enough privileges.
     *
     * @param GenericEvent $event event
     *
     * @return bool
     *
     * @throws AccessDeniedException
     */
    public function checkUserRights(GenericEvent $event): bool
    {
        /** @var Request $request */
        $request = $event->getArguments()['request'];

        $entity = $event->getArguments()['entity'];
        $action = $request->query->get('action');

        $this->adminAuthorizationChecker->checksUserAccess($entity, $action);

        return true;
    }
}
