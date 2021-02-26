<?php

namespace Lemo\Grid;

use ArrayAccess;
use ArrayIterator;
use Lemo\Grid\Adapter\AdapterInterface;
use Lemo\Grid\Column\ColumnInterface;
use Lemo\Grid\Column\ColumnPrepareAwareInterface;
use Lemo\Grid\Exception\InvalidArgumentException;
use Lemo\Grid\Platform\PlatformInterface;
use Lemo\Grid\Storage\StorageInterface;
use Lemo\Grid\Style\ColumnStyle;
use Lemo\Grid\Style\RowStyle;
use Traversable;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\PriorityQueue;

class Grid implements
    GridInterface,
    EventManagerAwareInterface
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
    protected $byName = [];

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * @var array|ColumnStyle[]
     */
    protected $columnStyles = [];

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @var GridFactory
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
     *
     * @var MvcEvent
     */
    protected $mvcEvent;

    /**
     * Grid name
     *
     * @var string
     */
    protected $name = 'grid';

    /**
     * Platform
     *
     * @var PlatformInterface
     */
    protected $platform;

    /**
     * @var array|RowStyle[]
     */
    protected $rowStyles = [];

    /**
     * Persistent storage handler
     *
     * @var Storage\StorageInterface
     */
    protected $storage = null;

    /**
     * Constructor
     *
     * @param  null|string            $name
     * @param  null|AdapterInterface  $adapter
     * @param  null|MvcEvent          $mvcEvent
     * @param  null|PlatformInterface $platform
     * @param  null|StorageInterface  $storage
     */
    public function __construct($name = null, $adapter = null, $mvcEvent = null, $platform = null, $storage = null)
    {
        $this->iterator = new PriorityQueue();

        if (null !== $name) {
            $this->setName($name);
        }

        if (null !== $adapter) {
            $this->setAdapter($adapter);
        }

        if (null !== $mvcEvent) {
            $this->setMvcEvent($mvcEvent);
        }

        if (null !== $platform) {
            $this->setPlatform($platform);
        }

        if (null !== $storage) {
            $this->setStorage($storage);
        }
    }

    /**
     * This function is automatically called when creating grid with factory. It
     * allows to perform various operations (add columns...)
     *
     * @return void
     */
    public function init()
    {
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
    public function add($column, array $flags = [])
    {
        if (
            is_array($column) ||
            ($column instanceof Traversable && !$column instanceof ColumnInterface)
        ) {
            $factory = $this->factory;

            if (!$factory instanceof GridFactory) {
                throw new InvalidArgumentException(sprintf(
                    "Grid '%s' has no GridFactory instance given",
                    $this->getName()
                ));
            }

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
        if(is_array($this->byName) && array_key_exists($column, $this->byName)) {
            $this->iterator->remove($this->byName[$column]);
            unset($this->byName[$column]);
        }

        if(is_array($this->columns) && array_key_exists($column, $this->columns)) {
            unset($this->columns[$column]);
        }

        return $this;
    }

    /**
     * Set columns
     *
     * @return Grid
     */
    public function setColumns(array $columns)
    {
        $this->clear();

        foreach ($columns as $column) {
            $this->add($column);
        }

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
     * Clear all attached columns
     *
     * @return Grid
     */
    public function clear()
    {
        $this->byName = [];
        $this->columns = [];
        $this->iterator = new PriorityQueue();

        return $this;
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
        $this->add($column, ['priority' => $priority]);

        return $this;
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
        $this->adapter->setGrid($this);

        return $this->adapter;
    }

    /**
     * Compose a grid factory to use when calling add() with a non-element
     *
     * @param  GridFactory $factory
     * @return Grid
     */
    public function setGridFactory(GridFactory $factory)
    {
        $this->factory = $factory;

        return $this;
    }

    /**
     * Retrieve composed grid factory
     * Lazy-loads one if none present.
     *
     * @return GridFactory
     */
    public function getGridFactory()
    {
        if (null === $this->factory) {
            $this->setGridFactory(new GridFactory());
        }

        return $this->factory;
    }

    /**
     * @param  EventManagerInterface $eventManager
     * @return Grid
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;

        return $this;
    }

    /**
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (null === $this->eventManager) {
            $this->eventManager = new EventManager();
        }

        return $this->eventManager;
    }

    /**
     * @param  MvcEvent $mvcEvent
     * @return Grid
     */
    public function setMvcEvent(MvcEvent $mvcEvent)
    {
        $this->mvcEvent = $mvcEvent;

        return $this;
    }

    /**
     *
     * @return MvcEvent
     */
    public function getMvcEvent()
    {
        return $this->mvcEvent;
    }

    /**
     * Set name
     *
     * @param  string $name
     * @return Grid
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
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
     * Set param
     *
     * @param  string $key
     * @param  mixed  $value
     * @return Grid
     */
    public function setParam($key, $value)
    {
        $content = $this->getStorage()->read($this->getName());

        if (!$content instanceof Traversable) {
            $content = new ArrayIterator();
        }

        // Modifi param in Platform
        $value = $this->getPlatform()->modifyParam($key, $value);

        if (false !== $value) {
            $content->offsetSet($key, $value);
            $this->getStorage()->write($this->getName(), $content);
        }

        return $this;
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

        // Set params to storage
        if ($this->getPlatform()->canUseParams($this, $params)) {
            foreach ($params as $key => $value) {
                $this->setParam($key, $value);
            }
        }

        return $this;
    }

    /**
     * Get param
     *
     * @param  string $key
     * @return mixed
     */
    public function getParam($key)
    {
        if ($this->hasParams() && $this->getStorage()->read($this->getName())->offsetExists($key)) {
            return $this->getStorage()->read($this->getName())->offsetGet($key);
        }

        return null;
    }

    /**
     * Get params from a specific namespace
     *
     * @return array
     */
    public function getParams()
    {
        return $this->getStorage()->read($this->getName());
    }

    /**
     * Exist param with given name?
     *
     * @param  string $key
     * @return bool
     */
    public function hasParam($key)
    {
        if ($this->hasParams()) {
            return $this->getStorage()->read($this->getName())->offsetExists($key);
        }

        return false;
    }

    /**
     * Whether a specific namespace has params
     *
     * @return bool
     */
    public function hasParams()
    {
        return (false === $this->getStorage()->isEmpty($this->getName())) ? true : false;
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
        $this->platform->setGrid($this);

        return $this->platform;
    }

    /**
     * Sets the storage handler
     *
     * @param  Storage\StorageInterface $storage
     * @return Grid
     */
    public function setStorage(Storage\StorageInterface $storage)
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * Returns the persistent storage handler
     *
     * Session storage is used by default unless a different storage adapter has been set.
     *
     * @return Storage\StorageInterface
     */
    public function getStorage()
    {
        if (null === $this->storage) {
            $this->setStorage(new Storage\Php\SessionStorage());
        }

        return $this->storage;
    }

    /**
     * @param  array|ColumnStyle $style
     * @return $this
     */
    public function addColumnStyle($style)
    {
        if ($style instanceof ColumnStyle) {
            $this->columnStyles[] = $style;
        } elseif (is_array($style)) {
            $this->columnStyles[] = new ColumnStyle($style);
        } else {
            throw new Exception\InvalidArgumentException(
                'The styles parameter must be an array or array of ColumnStyle'
            );
        }

        return $this;
    }

    /**
     * Set styles for column.
     *
     * @param  array|Traversable $styles
     * @return Grid
     * @throws Exception\InvalidArgumentException
     */
    public function setColumnStyles(array $styles)
    {
        foreach ($styles as $style) {
            $this->addColumnStyle($style);
        }

        return $this;
    }

    /**
     * Get defined column styles
     *
     * @return ColumnStyle[]
     */
    public function getColumnStyles()
    {
        return $this->columnStyles;
    }

    /**
     * Clear column styles
     *
     * @return Grid
     */
    public function clearColumnStyles()
    {
        $this->columnStyles = [];

        return $this;
    }

    /**
     * @param  array|RowStyle $style
     * @return $this
     */
    public function addRowStyle($style)
    {
        if ($style instanceof RowStyle) {
            $this->rowStyles[] = $style;
        } elseif (is_array($style)) {
            $this->rowStyles[] = new RowStyle($style);
        } else {
            throw new Exception\InvalidArgumentException(
                'The styles parameter must be an array or array of RowStyle'
            );
        }

        return $this;
    }

    /**
     * Set styles for an row.
     *
     * @param  array|Traversable $styles
     * @return Grid
     * @throws Exception\InvalidArgumentException
     */
    public function setRowStyles(array $styles)
    {
        foreach ($styles as $style) {
            $this->addRowStyle($style);
        }

        return $this;
    }

    /**
     * Get defined styles
     *
     * @return RowStyle[]
     */
    public function getRowStyles()
    {
        return $this->rowStyles;
    }

    /**
     * Clear styles
     *
     * @return Grid
     */
    public function clearRowStyles()
    {
        $this->rowStyles = [];

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

        $this->adapter = null;
        $this->byName = [];
        $this->columns = [];
        $this->container = null;
        $this->iterator = new PriorityQueue();
        $this->namespace = self::NAMESPACE_DEFAULT;
        $this->platform = null;
        $this->storage = null;

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
     * Check if is prepared
     *
     * @return bool
     */
    public function isPrepared()
    {
        return $this->isPrepared;
    }

    /**
     * Ensures state is ready for use
     * Prepares grid and any columns that require  preparation.
     *
     * @throws Exception\InvalidArgumentException
     * @return Grid
     */
    public function prepare()
    {
        if ($this->isPrepared) {
            return $this;
        }

        // Verify if was adapter set
        if (null === $this->adapter) {
            throw new Exception\InvalidArgumentException(sprintf(
                "Grid '%s' has no adapter",
                $this->getName()
            ));
        }

        // Verify if was platform set
        if (null === $this->platform) {
            throw new Exception\InvalidArgumentException(sprintf(
                "Grid '%s' has no platform set",
                $this->getName()
            ));
        }

        // Verify if was platform renderer set
        if (null === $this->platform->getRenderer()) {
            throw new Exception\InvalidArgumentException(sprintf(
                "Grid '%s' has no platform '%s' renderer",
                $this->getName(),
                get_class($this->getPlatform())
            ));
        }

        // Verify if was platform result set set
        if (null === $this->platform->getResultSet()) {
            throw new Exception\InvalidArgumentException(sprintf(
                "Grid '%s' has no platform '%s' result set",
                $this->getName(),
                get_class($this->getPlatform())
            ));
        }

        // Verify if was mvc event set
        if (null === $this->mvcEvent) {
            throw new Exception\InvalidArgumentException(sprintf(
                "Grid '%s' has no mvc event",
                $this->getName()
            ));
        }

        $this->init();

        $name = $this->getName();
        if ((null === $name || '' === $name)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s: grid is not named',
                __METHOD__
            ));
        }

        $this->setParams($this->getMvcEvent()->getRequest()->getQuery());

        // If the user wants to, elements names can be wrapped by the form's name
        foreach ($this->getColumns() as $column) {
            if ($column instanceof ColumnPrepareAwareInterface) {
                $column->prepareColumn($this);
            }
        }

        $this->getAdapter()->prepareAdapter();

        $this->isPrepared = true;

        return $this;
    }
}
