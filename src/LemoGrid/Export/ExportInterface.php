<?php

namespace LemoGrid\Export;

use LemoGrid\Column\ColumnInterface;

interface ExportInterface
{
    /**
     * Export data
     *
     * @param  ColumnInteface[] $columns
     * @param  array            $items
     */
    public function export(array $columns, array $items);
}
