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
        $this->defaults = [
            'value' => $config['defaultValue'] ?? "",
            'comparator' => $config['defaultComparator'] ?? "startswith"
        ];
        $this->additionalProperties = $config['additionalProperties'] ?? [];
        
        // must be an array
        if (!is_array($this->additionalProperties)) {
            $this->additionalProperties = [];
        }
    }


    public function apply($queryBuilder)
    {
        $value = trim($this->data["value"]);
        $comparator = $this->data["comparator"];

        // MAKE QUERY
        $query = $this->getPattern($comparator, $this->uniqueId, $this->alias, $this->columnName);
        foreach ($this->additionalProperties as $additionalCol) {
            $pattern = $this->getPattern($comparator, $this->uniqueId, $this->alias, $additionalCol);

            if ($pattern) {
                $query .= " OR " . $pattern; 
            }
        }

        $queryBuilder->andWhere($query);

        // SET QUERY PARAMETERS
        switch ($comparator) {
            case 'contains':
            case 'doesnotcontain':
                $queryBuilder->setParameter("val_" . $this->uniqueId, "%".$value."%");
                break;
            case 'startswith':
                $queryBuilder->setParameter("val_" . $this->uniqueId, $value."%");
                break;
            case 'endswith':
                $queryBuilder->setParameter("val_" . $this->uniqueId, "%".$value);
                break;
            case 'equals':
            case 'notequals':
                $queryBuilder->setParameter("val_" . $this->uniqueId, $value);
        }
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
