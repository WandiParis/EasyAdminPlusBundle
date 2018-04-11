<?php

namespace Wandi\EasyAdminPlusBundle\Exporter\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;

/**
 * Processes the template configuration to decide which template to use to
 * display each property in each view. It also processes the global templates
 * used when there is no entity configuration (e.g. for error pages).
 *
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class TemplateConfigPass implements ConfigPassInterface
{
    public function process(array $backendConfig)
    {
        $backendConfig = $this->processFieldTemplates($backendConfig);

        return $backendConfig;
    }

    /**
     * Determines the template used to render each backend element. This is not
     * trivial because templates can depend on the entity displayed and they
     * define an advanced override mechanism.
     *
     * @param array $backendConfig
     *
     * @return array
     */
    private function processFieldTemplates(array $backendConfig)
    {
        foreach ($backendConfig['entities'] as $entityName => $entityConfig) {
            foreach (array('export') as $view) {
                if (!array_key_exists($view, $backendConfig['entities'][$entityName])){
                    continue;
                }
                foreach ($entityConfig[$view]['fields'] as $fieldName => $fieldMetadata) {
                    if (null !== $fieldMetadata['template']) {
                        continue;
                    }

                    // needed to add support for immutable datetime/date/time fields
                    // (which are rendered using the same templates as their non immutable counterparts)
                    if ('_immutable' === substr($fieldMetadata['dataType'], -10)) {
                        $fieldTemplateName = 'field_'.substr($fieldMetadata['dataType'], 0, -10);
                    } else {
                        $fieldTemplateName = 'field_'.$fieldMetadata['dataType'];
                    }

                    // primary key values are displayed unmodified to prevent common issues
                    // such as formatting its values as numbers (e.g. `1,234` instead of `1234`)
                    if ($entityConfig['primary_key_field_name'] === $fieldName) {
                        $template = $entityConfig['templates']['field_id'];
                    // easyadminplus overrides
                    } elseif (file_exists('../vendor/wandi/easyadmin-plus-bundle/src/Resources/views/templates/field_' . $fieldMetadata['dataType'] . '.html.twig')) {
                        $template = '@WandiEasyAdminPlus/templates/field_' . $fieldMetadata['dataType'] . '.html.twig';
                    } elseif (array_key_exists($fieldTemplateName, $entityConfig['templates'])) {
                        $template = $entityConfig['templates'][$fieldTemplateName];
                    } else {
                        $template = '@WandiEasyAdminPlus/templates/label_null.html.twig';
                    }

                    $entityConfig[$view]['fields'][$fieldName]['template'] = $template;
                }
            }

            $backendConfig['entities'][$entityName] = $entityConfig;
        }

        return $backendConfig;
    }
}
