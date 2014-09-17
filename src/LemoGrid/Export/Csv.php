<?php

namespace LemoGrid\Export;

use LemoGrid\Column\ColumnInteface;

class Csv implements ExportInterface
{
    /**
     * Export data
     *
     * @param  ColumnInteface[] $columns
     * @param  array            $items
     */
    public function export(array $columns, array $items)
    {
        ob_clean();
        header('Content-Disposition: attachment; filename="grid.csv"');
        header('Content-Encoding: none');
        header('Content-Type: application/csv; charset=utf-8');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

        $columnNames = array();
        $columnToExport = array();
        foreach ($columns as $column) {
            $columnNames[] = iconv('utf-8', 'windows-1250', $column->getAttributes()->getLabel());
            $columnToExport[] = $column->getName();
        }

        $out = fopen('php://output', 'a');

        fputcsv($out, $columnNames, ';');

        foreach ($items as $item) {
            $values = array();
            foreach ($item as $key => $value) {
                if (in_array($key, $columnToExport)) {
                    $values[] = iconv('utf-8', 'windows-1250', $value);
                }
            }

            fputcsv($out, $values, ';');
        }

        fclose($out);
    }
} 