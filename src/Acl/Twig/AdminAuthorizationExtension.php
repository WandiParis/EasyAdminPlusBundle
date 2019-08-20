<?php

namespace Wandi\EasyAdminPlusBundle\Acl\Twig;

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

    public function isEasyAdminGranted(array $entity, string $actionName = 'list')
    {
        return $this->adminAuthorizationChecker->isEasyAdminGranted($entity, $actionName);
    }
}
