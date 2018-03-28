<?php

namespace Wandi\EasyAdminPlusBundle\Acl\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Wandi\EasyAdminPlusBundle\Acl\Helper\MenuHelper;

/**
 * @author Pierre-Charles Bertineau <pc.bertineau@alterphp.com>
 */
class MenuExtension extends AbstractExtension
{
    protected $menuHelper;

    public function __construct(MenuHelper $menuHelper)
    {
        $this->menuHelper = $menuHelper;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('prune_menu_items', array($this, 'pruneMenuItems')),
        );
    }

    public function pruneMenuItems(array $menuConfig, array $entitiesConfig)
    {
        return $this->menuHelper->pruneMenuItems($menuConfig, $entitiesConfig);
    }
}
