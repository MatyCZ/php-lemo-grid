<?php

namespace LemoGrid;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use LemoGrid\Adapter\AdapterInterface;
use LemoGrid\Platform\PlatformInterface;
use LemoGrid\Column\ColumnInterface;
use Traversable;

interface GridInterface extends
    Countable,
    GridFactoryAwareInterface,
    IteratorAggregate
{
    /**
     * Add an column
     *
     * $flags could contain metadata such as the alias under which to register
     * the column, order in which to prioritize it, etc.
     *
     * @param  array|ArrayAccess|Traversable|ColumnInterface $column Typically, only allow objects implementing ColumnInterface;
     *                                                   however, keeping it flexible to allow a factory-based form
     *                                                   implementation as well
     * @param  array $flags
     * @return GridInterface
     */
    public function add($column, array $flags = array());

    /**
     * Does the grid have an column by the given name?
     *
     * @param  string $column
     * @return bool
     */
    public function has($column);

    /**
     * Retrieve a named column
     *
     * @param  string $column
     * @return ColumnInterface
     */
    public function get($column);

    /**
     * Remove a named column
     *
     * @param  string $column
     * @return GridInterface
     */
    public function remove($column);

    /**
     * Retrieve all attached columns
     *
     * Storage is an implementation detail of the concrete class.
     *
     * @return array
     */
    public function getColumns();

    /**
     * Sets the grid adapter
     *
     * @param  AdapterInterface $adapter
     * @return GridInterface
     */
    public function setAdapter(AdapterInterface $adapter);

    /**
     * Returns the grid adapter
     *
     * @return AdapterInterface
     */
    public function getAdapter();

    /**
     * Set the name of this grid
     *
     * In most cases, this will proxy to the attributes for storage, but is
     * present to indicate that grids are generally named.
     *
     * @param  string $name
     * @return GridInterface
     */
    public function setName($name);

    /**
     * Retrieve the grid name
     *
     * @return string
     */
    public function getName();

    /**
     * Change the grid namespace for params
     *
     * @param  string $namespace
     * @return GridInterface
     */
    public function setNamespace($namespace);

    /**
     * Get the grid namespace for params
     *
     * @return string
     */
    public function getNamespace();

    /**
     * Set params
     *
     * @param  array|ArrayAccess|Traversable $params
     * @throws Exception\InvalidArgumentException
     * @return GridInterface
     */
    public function setParams($params);

    /**
     * Get params from a specific namespace
     *
     * @return array
     */
    public function getParams();

    /**
     * Whether a specific namespace has params
     *
     * @return bool
     */
    public function hasParams();

    /**
     * Set param
     *
     * @param  string $key
     * @param  mixed  $value
     * @return GridInterface
     */
    public function setParam($key, $value);

    /**
     * Get param
     *
     * @param  string $key
     * @return mixed
     */
    public function getParam($key);

    /**
     * Exist param with given name?
     *
     * @param  string $name
     * @return bool
     */
    public function hasParam($name);

    /**
     * Sets the grid platform
     *
     * @param  PlatformInterface $platform
     * @return GridInterface
     */
    public function setPlatform(PlatformInterface $platform);

    /**
     * Returns the grid platform
     *
     * @return PlatformInterface
     */
    public function getPlatform();

    /**
     * Check if is prepared
     *
     * @return bool
     */
    public function isPrepared();
}
