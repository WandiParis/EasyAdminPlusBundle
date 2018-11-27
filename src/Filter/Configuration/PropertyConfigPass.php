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
    // ne marche pas pour le moment
    private $autoFieldType = [
      'date'=> 'DateFilterType',
      'time'=> 'DateTimeFilterType',
      'datetime'=> 'DateTimeFilterType',
      'bigint' => 'NumberFilterType',
      'integer' => 'NumberFilterType',
      'smallint' => 'NumberFilterType',
      'decimal' => 'NumberFilterType',
      'float' => 'NumberFilterType',
      'varchar'  => 'StringFilterType'
    ];

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
                    if (isset($fieldConfig['filter_type'])) {
                        @trigger_error('filter_type is deprecated use type.', E_USER_DEPRECATED);
                        $class = $fieldConfig['filter_type'];
                    }elseif (isset($fieldConfig['type'])) {
                        $class = $fieldConfig['type'];
                    } else {
                        $class = 'StringFilterType';
                    }

                    if (strpos($class, '\\') === false) {
                      $class= "Lle\\EasyAdminPlusBundle\\Filter\\FilterType\\".$class;
                    }
                    $backendConfig['entities'][$entityName][$view]['fields'][$fieldName]['filter_type'] = $class;
                    $backendConfig['entities'][$entityName][$view]['fields'][$fieldName]['type'] = $class;
                    
                    $config = $fieldConfig['config'] ?? [];

                    $backendConfig['entities'][$entityName][$view]['fields'][$fieldName]['config'] = $config;

                }
            }
        }

        return $backendConfig;
    }

}
