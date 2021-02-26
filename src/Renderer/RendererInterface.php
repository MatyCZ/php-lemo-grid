<?php

namespace Lemo\Grid\Renderer;

use Lemo\Grid\Exception;
use Lemo\Grid\GridInterface;

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
