<?php

namespace LemoGrid\Renderer;

use LemoGrid\GridInterface;

abstract class AbstractRenderer implements RendererInterface
{
    /**
     * @var GridInterface
     */
    protected $grid;

    /**
     * Set grid instance
     *
     * @param  GridInterface $grid
     * @return AbstractRenderer
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