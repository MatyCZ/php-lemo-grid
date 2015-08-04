<?php

namespace LemoGrid\Adapter;

use LemoGrid\GridInterface;

interface AdapterInterface
{
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
     * Fetch data from adapter to platform resultset
     *
     * @return AdapterInterface
     */
    public function fetchData();

    /**
     * Find value for column
     *
     * @param  string $identifier
     * @param  array  $item
     * @param  int    $depth
     * @return null|string|int|array
     */
    public function findValue($identifier, array $item, $depth = 0);

    /**
     * Get number of current page
     *
     * @return int
     */
    public function getNumberOfPages();

    /**
     * Return count of items
     *
     * @return int
     */
    public function getCountOfItems();

    /**
     * Return count of items total
     *
     * @return int
     */
    public function getCountOfItemsTotal();
}
