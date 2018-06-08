<?php

namespace Lle\EasyAdminPlusBundle\Filter\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;

/**
 * Processes the entity fields to complete their configuration and to treat
 * some fields in a special way.
 *
 */
class PropertyConfigPass implements ConfigPassInterface
{

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
            foreach (array('filter') as $view) {
                if (!array_key_exists($view, $backendConfig['entities'][$entityName])){
                    continue;
                }
                foreach ($entityConfig[$view]['fields'] as $fieldName => $fieldConfig) {
                    $class = $fieldConfig['filter_type'];
                    if (strpos($class, '\\') === false) {
                      $class= "Lle\\EasyAdminPlusBundle\\Filter\\FilterType\\ORM\\".$class;
                    }
                    $params = $fieldConfig['params'];
                    $reflection_class = new \ReflectionClass($class);
                    $filterObj = $reflection_class->newInstanceArgs($params);
                    $backendConfig['entities'][$entityName][$view]['fields'][$fieldName]['code'] = $params[0];                    
                    $backendConfig['entities'][$entityName][$view]['fields'][$fieldName]['filter'] = $filterObj;
                    $backendConfig['entities'][$entityName][$view]['fields'][$fieldName]['template'] = $filterObj->getTemplate();
                }
            }
        }

        return $backendConfig;
    }

}
