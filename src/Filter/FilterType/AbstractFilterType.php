<?php
namespace Lle\EasyAdminPlusBundle\Filter\FilterType;

use Doctrine\ORM\EntityManagerInterface;
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
     *
     * @var null|string
     */
    protected $columnName = null;

    protected $hidden = false;

    /**
     *
     * @var null|string
     */
    protected $alias = null;

    protected $uniqueId = null;

    protected $label = null;

    protected $request = null;

    protected $data = null;

    protected $data_keys = [];

    protected $defaults = [];

    /**
     *
     * @var bool
     */
    protected $head = false;

    /**
     *
     * @param string $columnName
     *            The column name
     * @param string $alias
     *            The alias
     */
    public function init($columnName, $label = null, $alias = 'entity')
    {
        $this->columnName = $columnName;
        $this->uniqueId = str_replace('.', '_', $columnName);
        $this->alias = $alias;
        $this->label = $label ?? "label." . $columnName;
        $this->data = [];
        $this->data_keys = [
            'comparator',
            'value'
        ];
    }

    public function configure(array $config = [])
    {
        $this->hidden = $config['hidden'] ?? false;
        $this->head = $config['head'] ?? false;
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
            $var = 'filter_' . $k . '_' . str_replace('.', '_', $this->columnName);
            $val_get = $request->query->get($var, null);
            if (! is_null($val_get)) {
                $this->data[$k] = $val_get;
            } else {
                $val_post = $request->request->get($var, null);
                if (! is_null($val_post)) {
                    $this->data[$k] = $val_post;
                } else {
                    $this->data = [];
                }
            }
        }
    }


    public function initDefault()
    {
        foreach ($this->data_keys as $k) {
            if (! array_key_exists($k, $this->data) || !$this->data[$k]) {
                if (array_key_exists($k, $this->defaults)) {
                    $this->data[$k] = $this->defaults[$k] ?? null;
                } else {
                    $this->data[$k] = null;
                }
            }
        }
    }

    public function addJoin($queryBuilder)
    {
        $queryHelper = new QueryHelper();
        [
            $alias,
            $col
        ] = $queryHelper->getPath($queryBuilder, $queryBuilder->getRootAlias(), $this->columnName);
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

    /**
     *
     * @return boolean
     */
    public function isHead()
    {
        return $this->head;
    }

    /**
     *
     * @param boolean $head
     */
    public function setHead($head)
    {
        $this->head = $head;
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
        return '@LleEasyAdminPlus/filter/state/string_filter.html.twig';
    }

    public function __sleep()
    {
        return array(
            'columnName',
            'alias',
            'data'
        );
    }

    /**
     * Get the value of defaults
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * Set the value of defaults
     *
     * @return self
     */
    public function setDefaults($defaults)
    {
        if(!is_array($defaults)){
            $defaults = ['value'=>$defaults];
        }
        $this->defaults = array_merge($this->defaults,$defaults);

        return $this;
    }
}
