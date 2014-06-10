<?php

namespace LemoGrid\Adapter;

use LemoGrid\GridInterface;
use LemoGrid\ResultSet\ResultSetInterface;

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
     * Return data from adapter
     *
     * @return ResultSetInterface
     */
    public function getResultSet();
}
