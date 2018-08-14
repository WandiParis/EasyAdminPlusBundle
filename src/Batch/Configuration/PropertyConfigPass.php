<?php

namespace Lle\EasyAdminPlusBundle\Batch\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;
use Lle\EasyAdminPlusBundle\Service\ExportManager;

/**
 * Processes the entity fields to complete their configuration and to treat
 * some fields in a special way.
 *
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class PropertyConfigPass implements ConfigPassInterface
{
    private $defaultEntityActionConfig = array(
        // batch name (if 'null', autogenerate it)
        'name' => null,
        // batch label (if 'null', autogenerate it)
        'label' => null,
        // icon
        'icon' => null,
        // role
        'role' => null,
        // service
        'service' => null,
        // the path of the template used to render the field in 'show' and 'list' views
        'template' => null,
        // choices
        'choices' => null
    );

    public function process(array $backendConfig)
    {
        $backendConfig = $this->processFieldConfig($backendConfig);

        return $backendConfig;
    }

    /**
     * Completes the configuration of each field/property with the metadata
     * provided by Doctrine for each entity property.
     *
     * @param array $backendConfig
     *
     * @return array
     */
    private function processFieldConfig(array $backendConfig)
    {
        foreach ($backendConfig['entities'] as $entityName => $entityConfig) {
            foreach (array('list') as $view) {
                if (!array_key_exists('batchs', $backendConfig['entities'][$entityName][$view])){
                    continue;
                }
                foreach ($backendConfig['entities'][$entityName][$view]['batchs'] as $actionName => $actionConfig) {

                    $normalizedConfig = array_replace_recursive(
                        $this->defaultEntityActionConfig,
                        $actionConfig
                    );

                    $backendConfig['entities'][$entityName][$view]['batchs'][$actionName] = $normalizedConfig;
                }
            }
        }

        return $backendConfig;
    }

}
