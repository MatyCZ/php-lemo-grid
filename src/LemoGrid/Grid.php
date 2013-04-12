<?php

namespace LemoGrid;

use ArrayAccess;
use ArrayIterator;
use LemoGrid\Adapter\AbstractAdapter;
use LemoGrid\Adapter\AdapterInterface;
use LemoGrid\ColumnInterface;
use LemoGrid\Platform\PlatformInterface;
use Traversable;
use Zend\Feed\Reader\Collection;
use Zend\Json;
use Zend\Session\SessionManager;
use Zend\Session\Container as SessionContainer;
use Zend\Stdlib\PriorityQueue;

class Grid implements GridInterface
{
    /**
     * Default grid namespace
     */
    const NAMESPACE_DEFAULT = 'grid';

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
     * @var SessionContainer
     */
    protected $container;

    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @var PriorityQueue
     */
    protected $iterator;

    /**
     * Is the grid prepared?
     *
     * @var bool
     */
    protected $isPrepared = false;

    /**
     * Grid name
     *
     * @var string
     */
    protected $name;

    /**
     * Instance namespace
     *
     * @var string
     */
    protected $namespace;

    /**
     * Container parameters from query or session container
     *
     * @var array
     */
    protected $params = array();

    /**
     * Platform
     *
     * @var PlatformInterface
     */
    protected $platform;

    /**
     * @var SessionManager
     */
    protected $session;

    /**
     * Constructor
     *
     * @param  null|string            $name
     * @param  null|AdapterInterface  $adapter
     * @param  null|PlatformInterface $platform
     * @return Grid
     */
    public function __construct($name = null, AdapterInterface $adapter = null, $platform = null)
    {
        $this->iterator = new PriorityQueue();

        if (null !== $name) {
            $this->setName($name);
        }

        if (null !== $adapter) {
            $this->setAdapter($adapter);
        }

        if (null !== $platform) {
            $this->setPlatform($platform);
        }
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
            $column = $factory->createColumn($column);
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
     * Retrieve all attached columns
     *
     * Storage is an implementation detail of the concrete class.
     *
     * @return array
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
     * Make a deep clone of a grid
     *
     * @return void
     */
    public function __clone()
    {
        $items = $this->getIterator()->toArray(PriorityQueue::EXTR_BOTH);

        $this->byName    = array();
        $this->columns  = array();
        $this->container = null;
        $this->iterator  = new PriorityQueue();
        $this->namespace  = self::NAMESPACE_DEFAULT;
        $this->params  = array();
        $this->session = null;

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
     * Ensures state is ready for use
     *
     * Prepares any columns that require  preparation.
     *
     * @return Grid
     */
    public function prepare()
    {
        if ($this->isPrepared) {
            return $this;
        }

        // If the user wants to, elements names can be wrapped by the form's name
        foreach ($this->getIterator() as $column) {
            if ($column instanceof ColumnPrepareAwareInterface) {
                $column->prepareColumn($this);
            }
        }

        if(!$this->getPlatform()->getGrid() instanceof GridInterface) {
            $this->getPlatform()->setGrid($this);
        }

        $this->isPrepared = true;

        return $this;
    }

    public function renderData()
    {
        $adapter = $this->getAdapter();

        if (!$adapter instanceof AbstractAdapter) {
            throw new Exception\InvalidArgumentException('No Adapter instance given');
        }

        $items = array();
        $data = $adapter->setGrid($this)->getData();

        foreach ($data->getArrayCopy() as $index => $item) {
            $rowData = $item;

//            if($this->getOptions()->getTreeGrid() == true && $this->getOptions()->getTreeGridModel() == JqGridOptions::TREE_MODEL_NESTED) {
//                $item['leaf'] = ($item['rgt'] == $item['lft'] + 1) ? 'true' : 'false';
//                $item['expanded'] = 'true';
//            }
//
//            if($this->getOptions()->getTreeGrid() == true && $this->getOptions()->getTreeGridModel() == JqGridOptions::TREE_MODEL_ADJACENCY) {
//                $item['parent'] = $item['level'] > 0 ? $item['parent'] : 'NULL';
//                $item['leaf'] = $item['child_count'] > 0 ? 'false' : 'true';
//                $item['expanded'] = 'true';
//            }

            // Pridame radek
            $items[] = array(
                'id'   => $index +1,
                'cell' => array_values($rowData)
            );
        }

        ob_clean();
        echo Json\Encoder::encode(array(
            'page'    => $adapter->getNumberOfCurrentPage(),
            'total'   => $adapter->getCountOfItemsTotal(),
            'records' => $adapter->getCountOfItems(),
            'rows'    => $items,
        ));
        exit;
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
     * Get session container for grid
     *
     * @return SessionContainer
     */
    public function getContainer()
    {
        if ($this->container instanceof SessionContainer) {
            return $this->container;
        }

        $this->container = new SessionContainer('Grid', $this->getSessionManager());

        return $this->container;
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
     * Change the grid namespace for params
     *
     * @param  string $namespace
     * @return Grid
     */
    public function setNamespace($namespace = self::NAMESPACE_DEFAULT)
    {
        $this->namespace = (string) $namespace;

        return $this;
    }

    /**
     * Get the grid namespace for params
     *
     * @return string
     */
    public function getNamespace()
    {
        if (null === $this->namespace) {
            $this->namespace = $this->getName();
        }

        return $this->namespace;
    }

    /**
     * Set params
     *
     * @param  array|ArrayAccess|Traversable $params
     * @throws Exception\InvalidArgumentException
     * @return Grid
     */
    public function setParams($params)
    {
        if (!is_array($params) && !$params instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or Traversable argument; received "%s"',
                __METHOD__,
                (is_object($params) ? get_class($params) : gettype($params))
            ));
        }

        foreach ($params as $name => $value) {
            $this->setParam($name, $value);
        }

        return $this;
    }

    /**
     * Get params from a specific namespace
     *
     * @return array
     */
    public function getParams()
    {
        $this->getParamsFromContainer();

        if ($this->hasParams()) {
            return $this->params[$this->getNamespace()];
        }

        return array();
    }

    /**
     * Whether a specific namespace has params
     *
     * @return bool
     */
    public function hasParams()
    {
        $this->getParamsFromContainer();

        return isset($this->params[$this->getNamespace()]);
    }

    /**
     * Set param
     *
     * @param  string $name
     * @param  mixed  $value
     * @return Grid
     */
    public function setParam($name, $value)
    {
        $container = $this->getContainer();
        $namespace = $this->getNamespace();

        if (!isset($container->{$namespace}) || !($container->{$namespace} instanceof Traversable)) {
            $container->{$namespace} = new ArrayIterator();
        }
        if (!isset($this->params[$namespace]) || !($this->params[$namespace] instanceof Traversable)) {
            $this->params[$namespace] = new ArrayIterator();
        }

        // Modify params
        if ('filters' == $name) {
            if (is_array($value)) {
                $rules = $value;
            } else {
                $rules = Json\Decoder::decode(stripslashes($value), Json\Json::TYPE_ARRAY);
            }

            foreach ($rules['rules'] as $rule) {
                $this->setParam($rule['field'], $rule['data']);
            }
        }

        // Dont save grid name to Session
        if ('_name' != $name) {
            $container->{$namespace}->offsetSet($name, $value);
        }

        $this->params[$namespace]->offsetSet($name, $value);

        return $this;
    }

    /**
     * Get param
     *
     * @param  string $name
     * @return mixed
     */
    public function getParam($name)
    {
        $this->getParamsFromContainer();

        if (isset($this->params[$this->getNamespace()]) && $this->hasParam($name)) {
            return $this->params[$this->getNamespace()]->offsetGet($name);
        }

        return null;
    }

    /**
     * Exist param with given name?
     *
     * @param  string $name
     * @return bool
     */
    public function hasParam($name)
    {
        $this->getParamsFromContainer();

        if (isset($this->params[$this->getNamespace()])) {
            return $this->params[$this->getNamespace()]->offsetExists($name);
        }

        return false;
    }

    /**
     * Set the platform
     *
     * @param  PlatformInterface $platform
     * @return Grid
     */
    public function setPlatform(PlatformInterface $platform)
    {
        $this->platform = $platform;

        return $this;
    }

    /**
     * Get the platform
     *
     * @return PlatformInterface
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Set the session manager
     *
     * @param  SessionManager $manager
     * @return Grid
     */
    public function setSessionManager(SessionManager $manager)
    {
        $this->session = $manager;

        return $this;
    }

    /**
     * Retrieve the session manager
     *
     * If none composed, lazy-loads a SessionManager instance
     *
     * @return SessionManager
     */
    public function getSessionManager()
    {
        if (!$this->session instanceof SessionManager) {
            $this->setSessionManager(SessionContainer::getDefaultManager());
        }

        return $this->session;
    }

    /**
     * Pull params from the session container
     *
     * @return void
     */
    protected function getParamsFromContainer()
    {
        if (!empty($this->params)) {
            return;
        }

        $container = $this->getContainer();

        foreach ($container as $namespace => $params) {
            $this->params[$namespace] = $params;
        }
    }
}
