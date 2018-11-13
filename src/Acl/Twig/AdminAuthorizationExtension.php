<?php

namespace Lle\EasyAdminPlusBundle\Acl\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * @author Pierre-Charles Bertineau <pc.bertineau@alterphp.com>
 */
class AdminAuthorizationExtension extends AbstractExtension
{
    protected $adminAuthorizationChecker;

    public function __construct($adminAuthorizationChecker)
    {
        $this->adminAuthorizationChecker = $adminAuthorizationChecker;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('is_easyadmin_granted', array($this, 'isEasyAdminGranted')),
        );
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('prune_item_actions', array($this, 'pruneItemsActions')),
        );
    }

    public function isEasyAdminGranted(array $entity, string $actionName = 'list', $subject)
    {
        return $this->adminAuthorizationChecker->isEasyAdminGranted($entity, $actionName, $subject);
    }

    public function pruneItemsActions(array $itemActions, array $entity, array $forbiddenActions = [], $subject)
    {
        return array_filter($itemActions, function ($conf, $action) use ($entity, $forbiddenActions, $subject) {
            $authorize = !in_array($action, $forbiddenActions) && $this->isEasyAdminGranted($entity, $action, $subject);
            if(array_key_exists('if', $conf)) {

                $authorize = ($authorize && $subject && $subject->{$conf['if']}());
            }
            return $authorize;
        }, ARRAY_FILTER_USE_BOTH);
    }
}
