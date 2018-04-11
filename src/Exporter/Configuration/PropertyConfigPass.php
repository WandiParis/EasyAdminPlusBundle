<?php

namespace Wandi\EasyAdminPlusBundle\Exporter\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;

/**
 * Processes the entity fields to complete their configuration and to treat
 * some fields in a special way.
 *
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class PropertyConfigPass implements ConfigPassInterface
{
    private $defaultEntityFieldConfig = array(
        // CSS class or classes applied to form field or list/show property
        'css_class' => '',
        // date/time/datetime/number format applied to form field value
        'format' => null,
        // form field help message
        'help' => null,
        // form field label (if 'null', autogenerate it)
        'label' => null,
        // its value matches the value of 'dataType' for list/show and the value of 'fieldType' for new/edit
        'type' => null,
        // Symfony form field type (text, date, number, choice, ...) used to display the field
        'fieldType' => null,
        // Data type (text, date, integer, boolean, ...) of the Doctrine property associated with the field
        'dataType' => null,
        // is a virtual field or a real Doctrine entity property?
        'virtual' => false,
        // listings can be sorted according to the values of this field
        'sortable' => true,
        // the path of the template used to render the field in 'show' and 'list' views
        'template' => null,
        // the options passed to the Symfony Form type used to render the form field
        'type_options' => array(),
        // the name of the group where this form field is displayed (used only for complex form layouts)
        'form_group' => null,
    );

    private $defaultVirtualFieldMetadata = array(
        'columnName' => 'virtual',
        'fieldName' => 'virtual',
        'id' => false,
        'length' => null,
        'nullable' => false,
        'precision' => 0,
        'scale' => 0,
        'sortable' => false,
        'type' => 'text',
        'type_options' => array(
            'required' => false,
        ),
        'unique' => false,
        'virtual' => true,
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
            foreach (array('export') as $view) {
                if (!array_key_exists($view, $backendConfig['entities'][$entityName])){
                    continue;
                }
                foreach ($entityConfig[$view]['fields'] as $fieldName => $fieldConfig) {

                    if (array_key_exists($fieldName, $entityConfig['properties'])) {
                        $fieldMetadata = array_merge(
                            $entityConfig['properties'][$fieldName],
                            array('virtual' => false)
                        );
                    } else {
                        // this is a virtual field which doesn't exist as a property of
                        // the related entity. That's why Doctrine can't provide metadata for it
                        $fieldMetadata = array_merge(
                            $this->defaultVirtualFieldMetadata,
                            array('columnName' => $fieldName, 'fieldName' => $fieldName)
                        );
                    }

                    $normalizedConfig = array_replace_recursive(
                        $this->defaultEntityFieldConfig,
                        $fieldMetadata,
                        $fieldConfig
                    );

                    $normalizedConfig['dataType'] = $normalizedConfig['type'];

                    if (null === $normalizedConfig['format']) {
                        $normalizedConfig['format'] = $this->getFieldFormat($normalizedConfig['type'], $backendConfig);
                    }

                    $backendConfig['entities'][$entityName][$view]['fields'][$fieldName] = $normalizedConfig;
                }
            }
        }

        return $backendConfig;
    }

    /**
     * Returns the date/time/datetime/number format for the given field
     * according to its type and the default formats defined for the backend.
     *
     * @param string $fieldType
     * @param array  $backendConfig
     *
     * @return string The format that should be applied to the field value
     */
    private function getFieldFormat($fieldType, array $backendConfig)
    {
        if (in_array($fieldType, array('date', 'date_immutable', 'time', 'time_immutable', 'datetime', 'datetime_immutable', 'datetimetz'))) {
            // make 'datetimetz' use the same format as 'datetime'
            $fieldType = ('datetimetz' === $fieldType) ? 'datetime' : $fieldType;
            $fieldType = ('_immutable' === substr($fieldType, -10)) ? substr($fieldType, 0, -10) : $fieldType;

            return $backendConfig['formats'][$fieldType];
        }

        if (in_array($fieldType, array('bigint', 'integer', 'smallint', 'decimal', 'float'))) {
            return isset($backendConfig['formats']['number']) ? $backendConfig['formats']['number'] : null;
        }
    }
}
