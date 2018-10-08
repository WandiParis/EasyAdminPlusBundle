<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Lle\EasyAdminPlusBundle\Lib\QueryHelper;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * AbstractFilterType
 *
 * Abstract base class for all admin list filters
 */
abstract class AbstractFilterType implements FilterTypeInterface
{
    /**
     * @var null|string
     */
    protected $columnName = null;

    protected $em = null;

    protected $hidden = false;
    /**
     * @var null|string
     */
    protected $alias = null;
    protected $uniqueId = null;

    protected $label = null;

    protected $request = null;

    protected $data = null;
    protected $data_keys = [];
    /**
     * @param string $columnName The column name
     * @param string $alias The alias
     */
    public function __construct($columnName, $label= '', $config = array(),  $alias = 'entity')
    {
        $this->columnName = $columnName;
        $this->uniqueId = str_replace('.','_',$columnName);

        $this->alias = $alias;
        $this->label = $label ?? $columnName.".label";
        $this->hidden = $config['hidden'] ?? false;
        $this->data = [];
        $this->data_keys = ['comparator', 'value'];
    }

    public function getFilterLabel()
    {
        return $this->label;
    }
    
    public function getCode()
    {
        return $this->columnName;
    }

    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    public function updateDataFromRequest($request)
    {
        foreach ($this->data_keys as $k) {
            $var = 'filter_'.$k.'_'.str_replace('.','_',$this->columnName);
            $val_get = $request->query->get($var, null);
            if (!is_null($val_get)) {
                $this->data[$k] = $val_get;
            } else {
                $val_post = $request->request->get($var, null);
                $this->data[$k] = $val_post;
            }
            if (!array_key_exists($k, $this->data)) {
                $this->data[$k] = null;
            }
        }
    }

    public function setEm(EntityManager $em){
        $this->em = $em;
    }

    public function addJoin($queryBuilder) {
        $queryHelper = new QueryHelper();
        list($alias, $col) = $queryHelper->getPath($queryBuilder, $this->alias, $this->columnName);
        $this->alias = $alias;
        $this->columnName = $col;
    }

    /**
     * Returns empty string if no alias, otherwise make sure the alias has just one '.' after it.
     *
     * @return string
     */
    protected function getAlias()
    {
        return $this->alias;
    }

    public function isHidden()
    {
        return $this->hidden;
    }

    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }

    public function setRequest($request)
    {
        $this->request = $request;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getStateTemplate()
    {
        $converter = new CamelCaseToSnakeCaseNameConverter();
        $class = substr(get_called_class(), strrpos(get_called_class(), '\\') + 1);
        $template = str_replace('Type','',$class);
        $template = $converter->normalize($template);
        return '@LleEasyAdminPlus/FilterType/state/'.$template.'.html.twig';
    }

    public function getTemplate()
    {
        $converter = new CamelCaseToSnakeCaseNameConverter();
        $class = substr(get_called_class(), strrpos(get_called_class(), '\\') + 1);
        $template = str_replace('Type','',$class);
        $template = $converter->normalize($template);
        return '@LleEasyAdminPlus/FilterType/'.$template.'.html.twig';
    }
    public function __sleep()
    {
        return array('columnName', 'alias', 'data');
    }
}
