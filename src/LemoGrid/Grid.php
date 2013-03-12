<?php

namespace LemoGrid;

use LemoGrid\Adapter\AdapterInterface;
use LemoGrid\Column\ColumnInterface;
use Traversable;
use Zend\Feed\Reader\Collection;
use Zend\Json;
use Zend\Session\SessionManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\PriorityQueue;
use Zend\Stdlib\Parameters;
use Zend\View\Model\JsonModel;

class Grid implements GridInterface
{
    /**
     * Adapter
     *
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var array
     */
    protected $byName = array();

    /**
     * @var array
     */
    protected $columns = array();

    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @var PriorityQueue
     */
    protected $iterator;

    /**
     * Is the request a Javascript XMLHttpRequest?
     *
     * @var bool
     */
    protected $isXmlHttpRequest = false;

    /**
     * Grid name
     *
     * @var string
     */
    protected $name;

    /**
     * @var GridOptions
     */
    protected $options;

    /**
     * Parameter container responsible for query parameters
     *
     * @var Parameters
     */
    protected $params = array();

    /**
     * @var SessionManager
     */
    protected $sessionManager;

    /**
     * Constructor
     *
     * @param  null|string                        $name
     * @param  null|AdapterInterface              $adapter
     * @param  null|array|Traversable|GridOptions $options
     * @return \LemoGrid\Grid
     */
    public function __construct($name = null, AdapterInterface $adapter = null, $options = null)
    {
        $this->iterator = new PriorityQueue();

        if (null !== $name) {
            $this->setName($name);
        }

        if (null !== $adapter) {
            $this->setAdapter($adapter);
        }

        if (null !== $options) {
            $this->setOptions($options);
        }
    }

    /**
     * Set grid options
     *
     * @param  array|\Traversable|GridOptions $options
     * @throws Exception\InvalidArgumentException
     * @return Grid
     */
    public function setOptions($options)
    {
        if (!$options instanceof GridOptions) {
            if (is_object($options) && !$options instanceof Traversable) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Expected instance of LemoGrid\GridOptions; '
                    . 'received "%s"', get_class($options))
                );
            }

            $options = new GridOptions($options);
        }

        $this->options = $options;

        return $this;
    }

    /**
     * Get grid options
     *
     * @return GridOptions
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new GridOptions());
        }

        return $this->options;
    }

    /**
     * Sets the grid adapter
     *
     * @param  AdapterInterface $adapter
     * @return Grid
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Returns the grid adapter
     *
     * @return AdapterInterface|null
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Compose a grid factory to use when calling add() with a non-element
     *
     * @param  Factory $factory
     * @return Grid
     */
    public function setGridFactory(Factory $factory)
    {
        $this->factory = $factory;

        return $this;
    }

    /**
     * Retrieve composed grid factory
     *
     * Lazy-loads one if none present.
     *
     * @return Factory
     */
    public function getGridFactory()
    {
        if (null === $this->factory) {
            $this->setGridFactory(new Factory());
        }

        return $this->factory;
    }

    /**
     * Set if the request is a Javascript XMLHttpRequest
     *
     * @param  bool $flag
     * @return Grid
     */
    public function setIsXmlHttpRequest($flag)
    {
        $this->isXmlHttpRequest = $flag;

        return $this;
    }

    /**
     * Is the request a Javascript XMLHttpRequest?
     *
     * @return bool
     */
    public function getIsXmlHttpRequest()
    {
        return $this->isXmlHttpRequest;
    }

    /**
     * Set name
     *
     * @param  string $name
     * @return Grid
     */
    public function setName($name)
    {
        return $this->name = (string) $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set params
     *
     * @param  Parameters $params
     * @return Grid
     */
    public function setParams(Parameters $params)
    {
        if($params->offsetExists('filters')) {
            if(is_array($params->offsetGet('filters'))) {
                $rules = $params->offsetGet('filters');
            } else {
                $rules = Json\Decoder::decode(stripslashes($params->offsetGet('filters')), Json\Json::TYPE_ARRAY);
            }

            foreach($rules['rules'] as $rule) {
                $params->offsetSet($rule['field'], $rule['data']);
            }
        }

        $this->params = $params;

        return $this;
    }

    /**
     * Get params
     *
     * @return Parameters
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Get param
     *
     * @param  string $name
     * @return mixed
     */
    public function getParam($name)
    {
        return $this->params->offsetGet($name);
    }

    /**
     * Exist param with given name?
     *
     * @param  string $name
     * @return bool
     */
    public function hasParam($name)
    {
        if($this->params->offsetExists($name)) {
            return true;
        }

        return false;
    }

    /**
     * Add a column
     *
     * $flags could contain metadata such as the alias under which to register
     * the column, order in which to prioritize it, etc.
     *
     * @param  array|Traversable|ColumnInterface $column
     * @param  array                             $flags
     * @throws Exception\InvalidArgumentException
     * @return Grid
     */
    public function add($column, array $flags = array())
    {
        if (is_array($column)
        || ($column instanceof Traversable && !$column instanceof ColumnInterface)
        ) {
            $factory = $this->getGridFactory();
            $column = $factory->create($column);
        }

        if (!$column instanceof ColumnInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s requires that $column be an object implementing %s; received "%s"',
                __METHOD__,
            __NAMESPACE__ . '\ColumnInterface',
                (is_object($column) ? get_class($column) : gettype($column))
            ));
        }

        $name = $column->getName();
        if ((null === $name || '' === $name)
        && (!array_key_exists('name', $flags) || $flags['name'] === '')
        ) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s: column provided is not named, and no name provided in flags',
                __METHOD__
            ));
        }

        if (array_key_exists('name', $flags) && $flags['name'] !== '') {
            $name = $flags['name'];

            // Rename the column or fieldset to the specified alias
            $column->setName($name);
        }
        $order = 0;
        if (array_key_exists('priority', $flags)) {
            $order = $flags['priority'];
        }

        $this->iterator->insert($column, $order);
        $this->byName[$name] = $column;
        $this->columns[$name] = $column;

        return $this;
    }

    /**
     * Does the grid have a column by the given name?
     *
     * @param  string $column
     * @return bool
     */
    public function has($column)
    {
        return array_key_exists($column, $this->byName);
    }

    /**
     * Retrieve a named column
     *
     * @param  string $column
     * @return ColumnInterface
     */
    public function get($column)
    {
        if (!$this->has($column)) {
            return null;
        }

        return $this->byName[$column];
    }

    /**
     * Remove a named column
     *
     * @param  string $column
     * @return Grid
     */
    public function remove($column)
    {
        if (!$this->has($column)) {
            return $this;
        }

        $entry = $this->byName[$column];
        unset($this->byName[$column]);

        $this->iterator->remove($entry);

        unset($this->columns[$column]);

        return $this;
    }

    /**
     * Set/change the priority of a column
     *
     * @param  string $column
     * @param  int    $priority
     * @return Grid
     */
    public function setPriority($column, $priority)
    {
        $column = $this->get($column);
        $this->remove($column);
        $this->add($column, array('priority' => $priority));

        return $this;
    }

    /**
     * Retrieve all attached columns
     *
     * Storage is an implementation detail of the concrete class.
     *
     * @return array|Traversable
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Countable: return count of attached columns
     *
     * @return int
     */
    public function count()
    {
        return $this->iterator->count();
    }

    /**
     * IteratorAggregate: return internal iterator
     *
     * @return PriorityQueue
     */
    public function getIterator()
    {
        return $this->iterator;
    }

    /**
     * Return sort by column index
     *
     * @return string
     */
    public function getSortColumn()
    {
        if($this->hasParam('sidx')) {
            return $this->getParam('sidx');
        } else {
            return $this->getOptions()->getDefaultSortColumn();
        }
    }

    /**
     * Return sort direct
     *
     * @throws Exception\UnexpectedValueException
     * @return string
     */
    public function getSortDirect()
    {
        if($this->hasParam('sord')) {
            if(strtolower($this->getParam('sord')) != 'asc' && strtolower($this->getParam('sord')) != 'desc') {
                throw new Exception\UnexpectedValueException('Sort direct must be ' . 'asc' . ' or ' . 'desc' . '!');
            }

            return $this->getParam('sord');
        } else {
            return $this->getOptions()->getDefaultSortOrder();
        }
    }

    /**
     * Make a deep clone of a grid
     *
     * @return void
     */
    public function __clone()
    {
        $items = $this->iterator->toArray(PriorityQueue::EXTR_BOTH);

        $this->byName    = array();
        $this->columns  = array();
        $this->iterator  = new PriorityQueue();

        foreach ($items as $item) {
            $column = clone $item['data'];
            $name = $column->getName();

            $this->iterator->insert($column, $item['priority']);
            $this->byName[$name] = $column;

            if ($column instanceof ColumnInterface) {
                $this->columns[$name] = $column;
            }
        }
    }

    /**
     * Render grid
     *
     * @return string
     */
    public function renderData()
    {
        if($this->getIsXmlHttpRequest() && $this->getParam('_name') != $this->getName()) {
            return null;
        }

        $sessionContainer = new \Zend\Session\Container($this->getOptions()->getSessionNamespace());
        $query = $this->getParams();

        $sessionName = 'grid_' . $this->getName();

        if($this->getIsXmlHttpRequest() && $this->getParam('_name') != $this->getName()) {
            return null;
        }

        if($sessionContainer->offsetExists($sessionName)) {
            $session = $sessionContainer->offsetGet($sessionName);
        } else {
            $session = $sessionContainer->offsetSet($sessionName, array());
        }

        if(true === $this->getIsXmlHttpRequest()) {
            if(isset($session['loaded']) && $session['loaded'] == true) {
                if(isset($session['filters']) && $session['loaded'] == true && !isset($query['filters'])) {
                    $query['filters'] = $session['filters'];
                }

                $session['page'] = @$query['page'];
                $session['rows'] = @$query['rows'];
                $session['sidx'] = @$query['sidx'];
                $session['sord'] = @$query['sord'];
                $session['filters'] = @$query['filters'];

            } else {
                $session['loaded'] = true;

                if(isset($session['page'])) {
                    $query['page'] = $session['page'];
                    $query['rows'] = $session['rows'];
                    $query['sidx'] = $session['sidx'];
                    $query['sord'] = $session['sord'];
                    $query['filters'] = $session['filters'];
                }
            }
        } else {
            unset($session['loaded']);

            if(isset($session['page'])) {
                $query['page'] = $session['page'];
                $query['rows'] = $session['rows'];
                $query['sidx'] = $session['sidx'];
                $query['sord'] = $session['sord'];
                $query['filters'] = $session['filters'];
            }
        }

        $sessionContainer->offsetSet($sessionName, $session);
        $this->setParams($query);

    // ===== RENDERING =====

        if(true == $this->getIsXmlHttpRequest()) {
            $items = array();
            $data = $this->getAdapter()->setGrid($this)->getData();

            foreach($data->getArrayCopy() as $index => $item) {
                $rowData = $item;

                if($this->getOptions()->getTreeGrid() == true && $this->getOptions()->getTreeGridModel() == GridOptions::TREE_MODEL_NESTED) {
                    $item['leaf'] = ($item['rgt'] == $item['lft'] + 1) ? 'true' : 'false';
                    $item['expanded'] = 'true';
                }

                if($this->getOptions()->getTreeGrid() == true && $this->getOptions()->getTreeGridModel() == GridOptions::TREE_MODEL_ADJACENCY) {
                    $item['parent'] = $item['level'] > 0 ? $item['parent'] : 'NULL';
                    $item['leaf'] = $item['child_count'] > 0 ? 'false' : 'true';
                    $item['expanded'] = 'true';
                }

                // Pridame radek
                $items[] = array(
                    'id' => $index +1,
                    'cell' => array_values($rowData)
                );
            }

            @ob_clean();
            echo Json\Encoder::encode(array(
                'page' => $this->getAdapter()->getNumberOfCurrentPage(),
                'total' => $this->getAdapter()->getCountOfItemsTotal(),
                'records' => $this->getAdapter()->getCountOfItems(),
                'rows' => $items,
            ));
            exit;
        }

        return $this;
    }
}
