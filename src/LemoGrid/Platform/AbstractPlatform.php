<?php

namespace LemoGrid\Platform;

use LemoGrid\GridInterface;

abstract class AbstractPlatform implements PlatformInterface
{
    /**
     * @var GridInterface
     */
    protected $grid;

    /**
     * Set grid instance
     *
     * @param  GridInterface $grid
     * @return AbstractPlatform
     */
    public function setGrid(GridInterface $grid)
    {
        $this->grid = $grid;

        return $this;
    }

    /**
     * Get grid instance
     *
     * @return GridInterface
     */
    public function getGrid()
    {
        return $this->grid;
    }
}
