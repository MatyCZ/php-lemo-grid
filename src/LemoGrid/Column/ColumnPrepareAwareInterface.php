<?php

namespace LemoGrid\Column;

use LemoGrid\GridInterface;

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
