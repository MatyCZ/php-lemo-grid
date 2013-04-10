<?php

namespace LemoGrid\Adapter;

use LemoGrid\Collection\Data;
use LemoGrid\GridInterface;

interface AdapterInterface
{
    /**
     * Return data from adapter
     *
     * @return Data
     */
    public function getData();

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
}
