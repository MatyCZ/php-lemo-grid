<?php

namespace Lemo\Grid\Column;

use Lemo\Grid\GridInterface;

interface ColumnPrepareAwareInterface
{
    /**
     * Prepare the grid column (mostly used for rendering purposes)
     *
     * @param  GridInterface $grid
     * @return mixed
     */
    public function prepareColumn(GridInterface $grid);
}
