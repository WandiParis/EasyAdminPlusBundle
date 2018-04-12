<?php

namespace Wandi\EasyAdminPlusBundle\Exporter\Configuration;

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
        $backendConfig = $this->normalizePropertyConfig($backendConfig);

        return $backendConfig;
    }

    /**
     * Fields can be defined using two different formats:.
     *
     * # Config format #1: simple configuration
     * easy_admin:
     *     Client:
     *         # ...
     *         list:
     *             fields: ['id', 'name', 'email']
     *
     * # Config format #2: extended configuration
     * easy_admin:
     *     Client:
     *         # ...
     *         list:
     *             fields: ['id', 'name', { property: 'email', label: 'Contact' }]
     *
     * This method processes both formats to produce a common form field configuration
     * format used in the rest of the application.
     *
     * @param array $backendConfig
     *
     * @return array
     */
    private function normalizePropertyConfig(array $backendConfig)
    {
        foreach ($backendConfig['entities'] as $entityName => $entityConfig) {
            $designElementIndex = 0;
            foreach (array('export') as $view) {
                if (!array_key_exists($view, $backendConfig['entities'][$entityName])){
                    continue;
                }
                $fields = array();
                foreach ($entityConfig[$view]['fields'] as $i => $field) {
                    if (!is_string($field) && !is_array($field)) {
                        throw new \RuntimeException(sprintf('The values of the "fields" option for the "%s" view of the "%s" entity can only be strings or arrays.', $view, $entityConfig['class']));
                    }

                    if (is_string($field)) {
                        // Config format #1: field is just a string representing the entity property
                        $fieldConfig = array('property' => $field);
                    } else {
                        // Config format #1: field is an array that defines one or more
                        // options. Check that either 'property' or 'type' option is set
                        if (!array_key_exists('property', $field) && !array_key_exists('type', $field)) {
                            throw new \RuntimeException(sprintf('One of the values of the "fields" option for the "%s" view of the "%s" entity does not define neither of the mandatory options ("property" or "type").', $view, $entityConfig['class']));
                        }

                        $fieldConfig = $field;
                    }

                    // for 'image' type fields, if the entity defines an 'image_base_path'
                    // option, but the field does not, use the value defined by the entity
                    if (isset($fieldConfig['type']) && 'image' === $fieldConfig['type']) {
                        if (!isset($fieldConfig['base_path']) && isset($entityConfig['image_base_path'])) {
                            $fieldConfig['base_path'] = $entityConfig['image_base_path'];
                        }
                    }

                    // for 'file' type fields, if the entity defines an 'file_base_path'
                    // option, but the field does not, use the value defined by the entity
                    if (isset($fieldConfig['type']) && 'file' === $fieldConfig['type']) {
                        if (!isset($fieldConfig['base_path']) && isset($entityConfig['file_base_path'])) {
                            $fieldConfig['base_path'] = $entityConfig['file_base_path'];
                        }
                    }

                    // fields that don't define the 'property' name are special form design elements
                    $fieldName = isset($fieldConfig['property']) ? $fieldConfig['property'] : '_easyadmin_form_design_element_'.$designElementIndex;
                    $fields[$fieldName] = $fieldConfig;
                    ++$designElementIndex;
                }

                $backendConfig['entities'][$entityName][$view]['fields'] = $fields;
            }
        }

        return $backendConfig;
    }
}
