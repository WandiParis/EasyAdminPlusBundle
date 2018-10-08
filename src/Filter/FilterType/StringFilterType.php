<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType;

use Symfony\Component\HttpFoundation\Request;

/**
 * StringFilterType
 */
class StringFilterType extends AbstractFilterType
{
    /**
     * @var string
     */
    private $defaultValue;

    /**
     * @var string
     */
    private $defaultComparator;

    /**
     * @var array
     */
    private $additionalProperties;

    /**
     * @param string $columnName The column name
     * @param string $alias      The alias
     */
    public function __construct($columnName, $label, $config = array(), $alias = 'entity')
    {
        parent::__construct($columnName, $label, $config, $alias);
        $this->defaultValue = $config['defaultValue'] ?? "";
        $this->defaultComparator = $config['defaultComparator'] ?? "startswith";
        $this->additionalProperties = $config['additionalProperties'] ?? [];
        
        // must be an array
        if (!is_array($this->additionalProperties)) {
            $this->additionalProperties = [];
        }
    }


    public function apply($queryBuilder)
    {
        if (!array_key_exists("value", $data) || !isset($data["value"]) ) {
            $data["value"] = $this->defaultValue;
        }

        $value = trim($data["value"]) ?? $this->defaultValue;
        $comparator = $data["comparator"] ?? "startswith";

        // MAKE QUERY
        $query = $this->getPattern($comparator, $uniqueId, $alias, $col);
        foreach ($this->additionalProperties as $additionalCol) {
            $pattern = $this->getPattern($comparator, $uniqueId, $alias, $additionalCol);

            if ($pattern) {
                $query .= " OR " . $pattern; 
            }
        }

        $this->queryBuilder->andWhere($query);

        // SET QUERY PARAMETERS
        switch ($comparator) {
            case 'contains':
            case 'doesnotcontain':
                $this->queryBuilder->setParameter("val_" . $uniqueId, "%".$value."%");
                break;
            case 'startswith':
                $this->queryBuilder->setParameter("val_" . $uniqueId, $value."%");
                break;
            case 'endswith':
                $this->queryBuilder->setParameter("val_" . $uniqueId, "%".$value);
                break;
            case 'equals':
            case 'notequals':
                $this->queryBuilder->setParameter("val_" . $uniqueId, $value);
        }
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return '@LleEasyAdminPlus/FilterType/stringFilter.html.twig';
    }

    /**
     * @return string
     * @param string comparator the comparator to use
     * @param string parameter the query parameter to set
     */
    private function getPattern($comparator, $uniqueId, $alias, $col)
    {
        $pattern = null;
        switch ($comparator) {
            case "isnull":
                $pattern = $alias . $col .' IS NULL OR '.$alias . $col ." = '' ";
                break;
            case "isnotnull":
                $pattern = $alias . $col .' IS NOT NULL AND '.$alias . $col ." <> '' ";
                break;
            case "equals":
                $pattern = $alias . $col .' = :val_' . $uniqueId;
                break;
            case "notequals":
                $pattern = $alias . $col .' != :val_' . $uniqueId;
                break;
            case "contains":
            case "endswith":
            case "startswith":
                $pattern = $alias . $col .' LIKE :val_' . $uniqueId;
                break;
            case "doesnotcontain":
                $pattern = $alias . $col .' NOT LIKE :val_' . $uniqueId;
        }

        return $pattern ? "(".$pattern.")" : null;
    }
}
