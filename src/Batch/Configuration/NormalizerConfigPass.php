<?php

namespace Lle\EasyAdminPlusBundle\Batch\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Normalizes the different configuration formats available for entities, views,
 * actions and properties.
 *
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class NormalizerConfigPass implements ConfigPassInterface
{
    public function process(array $backendConfig)
    {
        $backendConfig = $this->normalizeBatchConfig($backendConfig);

        return $backendConfig;
    }

    private function normalizeBatchConfig(array $backendConfig)
    {
        $views = array('list');

        foreach ($backendConfig['entities'] as $entityName => $entityConfig) {
            $designElementIndex = 0;
            foreach ($views as $view) {
                $batchs = array();

                $backendConfig['entities'][$entityName][$view]['hasBatchActions'] = false;

                if(array_key_exists('batchs', $backendConfig['entities'][$entityName][$view])) {
                    $backendConfig['entities'][$entityName][$view]['hasBatchActions'] = true ;
                    if (!is_array($backendConfig['entities'][$entityName][$view]['batchs'])) {
                        throw new \InvalidArgumentException(sprintf('The "batchs" configuration for the "%s" view of the "%s" entity must be an array (a string was provided).', $view, $entityName));
                    }

                    foreach ($entityConfig[$view]['batchs'] as $i => $actionConfig) {


                        // fields that don't define the 'property' name are special form design elements
                        $actionName = isset($actionConfig['name']) ? $actionConfig['name'] : '_easyadmin_action_batch_'.$designElementIndex;
                        $batchs[$actionName] = $actionConfig;
                        ++$designElementIndex;
                    }

                    $backendConfig['entities'][$entityName][$view]['batchs'] = $batchs;
                }
            }
        }

        return $backendConfig;
    }

   
}
