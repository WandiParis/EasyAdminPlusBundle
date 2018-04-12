<?php

namespace Wandi\EasyAdminPlusBundle\Acl\Helper;

/**
 * @author Pierre-Charles Bertineau <pc.bertineau@alterphp.com>
 */
class MenuHelper
{
    protected $adminAuthorizationChecker;

    public function __construct($adminAuthorizationChecker)
    {
        $this->adminAuthorizationChecker = $adminAuthorizationChecker;
    }

    public function pruneMenuItems(array $menuConfig, array $entitiesConfig): array
    {
        $menuConfig = $this->pruneAccessDeniedEntries($menuConfig, $entitiesConfig);
        $menuConfig = $this->pruneEmptyFolderEntries($menuConfig);
        $menuConfig = $this->reindexMenuItems(array_values($menuConfig));

        return $menuConfig;
    }

    protected function pruneAccessDeniedEntries(array $menuConfig, array $entitiesConfig): array
    {
        foreach ($menuConfig as $key => $entry) {
            if (
                'entity' == $entry['type']
                && isset($entry['entity'])
                && !$this->adminAuthorizationChecker->isEasyAdminGranted(
                    $entitiesConfig[$entry['entity']],
                    isset($entry['params']) && isset($entry['params']['action']) ? $entry['params']['action'] : 'list'
                )
            ) {
                unset($menuConfig[$key]);
                continue;
            }

            if (isset($entry['children']) && is_array($entry['children'])) {
                $menuConfig[$key]['children'] = $this->pruneAccessDeniedEntries($entry['children'], $entitiesConfig);
            }
        }

        return $menuConfig;
    }

    protected function pruneEmptyFolderEntries(array $menuConfig): array
    {
        foreach ($menuConfig as $key => $entry) {
            if (isset($entry['children'])) {
                // Starts with sub-nodes in order to empty after possible children pruning...
                $menuConfig[$key]['children'] = $this->pruneEmptyFolderEntries($entry['children']);

                if ('empty' === $entry['type'] && empty($entry['children'])) {
                    unset($menuConfig[$key]);
                    continue;
                }
            }
        }

        return $menuConfig;
    }

    /**
     * Sadly, as Javier manages the item currently selected in the menu
     * by dealing in back-end the parameters `menuIndex` and `submenuIndex`
     * we've to reindex recursively all the array after pruned it :/
     *
     * @param array $menuConfig
     * @return array
     */
    protected function reindexMenuItems(array $menuConfig): array
    {
        for($i=0, $countItems=count($menuConfig) ; $i<$countItems ; $i++){
            $menuConfig[$i]['menu_index'] = $i;
            $menuConfig[$i]['submenu_index'] = '-1';

            if (array_key_exists('children', $menuConfig[$i]) && is_array($menuConfig[$i]['children'])) {
                $menuConfig[$i]['children'] = array_values($menuConfig[$i]['children']);
                for ($j = 0, $countSubItems = count($menuConfig[$i]['children']); $j < $countSubItems; $j++) {
                    $menuConfig[$i]['children'][$j]['menu_index'] = $i;
                    $menuConfig[$i]['children'][$j]['submenu_index'] = $j;
                }
            }
        }

        return $menuConfig;
    }
}
