<?php

namespace LemoGrid\Adapter;

use LemoGrid\GridInterface;
use LemoGrid\ResultSet\ResultSetInterface;

interface AdapterInterface
{
    /**
     * Name of adapter
     *
     * @return string
     */
    public function getName();

    /**
     * Set grid instance
     *
     * @param  GridInterface $grid
     * @return AdapterInterface
     */
    public function setGrid(GridInterface $grid);

    /**
     * Get grid instance
     *
     * @return GridInterface
     */
    public function getGrid();

    /**
     * Return data from adapter
     *
     * @return ResultSetInterface
     */
    public function getResultSet();

    /**
     * Find value for column
     *
     * @param  string $identifier
     * @param  array  $item
     * @param  int    $depth
     * @return null|string|int|array
     */
    public function findValue($identifier, array $item, $depth = 0);
}
