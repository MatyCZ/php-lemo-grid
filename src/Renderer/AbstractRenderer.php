<?php

namespace Lemo\Grid\Renderer;

use Lemo\Grid\GridInterface;

abstract class AbstractRenderer implements RendererInterface
{
    /**
     * @var GridInterface|null
     */
    protected ?GridInterface $grid = null;

    /**
     * Set grid instance
     *
     * @param  GridInterface $grid
     * @return self
     */
    public function setGrid(GridInterface $grid): self
    {
        $this->grid = $grid;

        return $this;
    }

    /**
     * Get grid instance
     *
     * @return GridInterface|null
     */
    public function getGrid(): ?GridInterface
    {
        return $this->grid;
    }
}