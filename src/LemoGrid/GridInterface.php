<?php

namespace LemoGrid;

use Countable;
use IteratorAggregate;

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
     * @param  array|\Traversable|ColumnInterface $column Typically, only allow objects implementing ColumnInterface;
     *                                                    however, keeping it flexible to allow a factory-based form
     *                                                    implementation as well
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
     * @return array|\Traversable
     */
    public function getColumns();
}
