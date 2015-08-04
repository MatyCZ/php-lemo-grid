<?php

namespace LemoGrid\Renderer;

use LemoGrid\Exception;
use LemoGrid\GridInterface;

interface RendererInterface
{
    /**
     * Set grid instance
     *
     * @param  GridInterface $grid
     * @return RendererInterface
     */
    public function setGrid(GridInterface $grid);

    /**
     * Get grid instance
     *
     * @return GridInterface
     */
    public function getGrid();

    /**
     * Render data
     *
     * @return void
     */
    public function renderData();
}
