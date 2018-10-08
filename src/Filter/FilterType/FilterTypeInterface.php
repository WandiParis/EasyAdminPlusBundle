<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType;

use Symfony\Component\HttpFoundation\Request;

/**
 * FilterTypeInterface
 */
interface FilterTypeInterface
{

    public function __construct($columnName, $label, $config, $alias = 'entity');

    /**
     * @param array  $data     Data
     * @param string $uniqueId The identifier
     */
    public function apply($query_builder);

    /**
     * @return string
     */
    public function getTemplate();

    public function updateDataFromRequest($request);

}
