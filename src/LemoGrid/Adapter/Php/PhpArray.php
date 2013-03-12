<?php

namespace LemoGrid\Adapter\Php;

use LemoGrid\Adapter\AbstractAdapter;

class ArrayAdapter extends AbstractAdapter
{
    protected $_array = null;

    protected $_arrayMap = null;

    /**
     * @var array
     */
    protected $_internalMap = array();

    /**
     * Constuctor
     *
     * @param array      $dql     Data as key => value or only values
     * @param array|null $arrayMap  Data map
     */
    public function __construct(array $dql, array $arrayMap = null)
    {
        $this->_array = $dql;
        $this->_arrayMap = $arrayMap;
    }

    /**
     * Load data
     *
     * @return array
     */
    public function getData()
    {
        $grid = $this->getGrid();
        $array = $this->getArray();
        $arrayMap = $this->getArrayMap();

        foreach($array as $item)
        {
            $colIndex = 0;
            $rowData = array();

            foreach($grid->getColumns() as $column)
            {
                if($this->getArrayMap() === null) {
                    $value = isset($item[$column->getIndex()]) ? $item[$column->getIndex()] : null;

                    $this->_internalMap[$column->getIndex()] = $colIndex;
                } else {

                    $item = array_values($item);

                    if(in_array($column->getIndex(), $arrayMap)) {
                        $value = $item[array_search($column->getIndex(),$arrayMap)];
                        $this->_internalMap[$column->getIndex()] = array_search($column->getIndex(),$arrayMap);
                    } else {
                        $value = null;
                    }
                }

                //$condition = $column->getCondition();
                $condition = null;

                if($condition == null) {
                    $value = $column->render($value);
                } else {
                    $conditionValue = $this->_getRowData($condition['column'], $item, $relations);

                    switch($condition['expression']) {
                        case '==':
                            if($conditionValue == $condition['value']) {
                                $value = $column->render($value);
                            }
                            break;
                        case '!=':
                            if($conditionValue != $condition['value']) {
                                $value = $column->render($value);
                            }
                            break;
                        case '>':
                            if($conditionValue > $condition['value']) {
                                $value = $column->render($value);
                            }
                            break;
                        case '>=':
                            if($conditionValue >= $condition['value']) {
                                $value = $column->render($value);
                            }
                            break;
                        case '<=':
                            if($conditionValue <= $condition['value']) {
                                $value = $column->render($value);
                            }
                            break;
                        case '<':
                            if($conditionValue < $condition['value']) {
                                $value = $column->render($value);
                            }
                            break;
                    }
                }

                // Projdeme data a nahradime data ve formatu %xxx%
                if(preg_match_all('/%([a-zA-Z0-9\._-]+)%/', $value, $matches)) {
                    foreach($matches[0] as $key => $match) {
                        $value = str_replace($matches[0][$key], $item[$matches[1][$key]], $value);
                    }
                }

                $rowData[] = $value;

                $colIndex++;
            }

            $data[] = $rowData;
        }

        $this->_records = count($data);

        $data = $this->_sortCollection($data);

        $data = array_slice($data, $grid->getRecordsPerPage() * $grid->getAdapter()->getNumberOfCurrentPage() - $grid->getRecordsPerPage(), $grid->getRecordsPerPage());

        $this->_recordsFiltered = count($data);

        return $data;
    }

    private function _filterCollection($items, $columns, $query)
    {
        if($items == null OR $columns == null OR $query == null) {
            return $items;
        }

        $filteredItems = null;

        // Aplikujeme filtr na zdrojova data
        $a = 1;
        $b = 1;
        foreach($items as $item)
        {
            $item['grid_id'] = $a;

            $valid = true;

            foreach($columns as $column)
            {
                // Pokud je mezi GET parametry filtr na dany sloupec
                if(isset($query[$column->getName()]) AND $query[$column->getName()] != '') {
                    // Pokud byl nalezen sloupec primo mezi daty
                    if(isset($item[$column->getSourceField()])) {
                        // Pokud zaznam ve sloupci neodpovida filtru
                        if(!preg_match('/' . strtolower($query[$column->getName()]) . '/', strtolower($item[$column->getSourceField()]))) {
                            $valid = false;
                            break;
                        }
                    } else {

                        $relationName = $column->getRelationName();
                        $sourceField = $column->getSourceField();
                        $string = null;

                        if($relationName == null AND isset($item['Translation']) OR $relationName == 'Translation' AND isset($item['Translation'])) {
                            foreach($item['Translation'] as $lang => $values)
                            {
                                if(isset($values[$sourceField])) {
                                    $string .= $values[$sourceField] . ' ';
                                }
                            }
                        }

                        if($relationName != null AND isset($item[$relationName])) {
                            // Pokud byla nalezena prekladova tabulka
                            if(isset($item[$relationName]['Translation'])) {
                                foreach($item[$relationName]['Translation'] as $lang => $values)
                                {
                                    if(isset($values[$sourceField])) {
                                        $string .= $values[$sourceField] . ' ';
                                    }
                                }
                            }

                            // Pokud byl nalezen odpovidajici zaznam v relaci
                            if(isset($item[$relationName][$sourceField])) {
                                $string .= $item[$relationName][$sourceField] . ' ';
                            }
                        }

                        if(!preg_match('/' . mb_strtolower($query[$column->getName()]) . '/', mb_strtolower($string))) {
                            $valid = false;
                            break;
                        }
                    }
                }
            }

            // Pokud zaznam odpovida vsem filtrum
            if($valid === true) {
                $item['grid_order'] = $b;
                $filteredItems[] = $item;

                $b++;
            }

            $a++;
        }

        return $filteredItems;
    }

    private function _sortCollection($records)
    {
        if($records == null) {
            return $records;
        }

        $queryParams = $this->getGrid()->getParams();

        if(isset($queryParams['sortColumn'])) {
            $sortBy = $queryParams['sortColumn'];

            if(isset($queryParams['sortDirect'])) {
                if(strtolower($queryParams['sortDirect']) != 'asc' AND strtolower($queryParams['sortDirect']) != 'desc') {
                    throw new Lemo_Grid_Exception('Sort direct must be ' . 'asc' . ' or ' . 'desc' . '!');
                }

                $sortDirect = $queryParams['sortDirect'];
            } else {
                $sortDirect = 'asc';
            }
        } else {
            $sortBy = $defaultSort['sortBy'];
            $sortDirect = $defaultSort['sortDirect'];
        }

        foreach(array_keys($records) as $key) {
            $temp[$key] = $records[$key][$this->_internalMap[$sortBy]];

            if(strtolower($sortDirect) == 'asc') {
                asort($temp);
            } else {
                arsort($temp);
            }
        }

        foreach(array_keys($temp) as $key)
        {
            if(is_numeric($key)) {
                $sortedItems[] = $records[$key];
            } else {
                $sortedItems[$key] = $records[$key];
            }
        }

        return $sortedItems;
    }

    /**
     * Get data source array
     *
     * @return array|null
     */
    public function getArray()
    {
        return $this->_array;
    }

    /**
     * Get data source array map
     *
     * @return array|null
     */
    public function getArrayMap()
    {
        return array_values($this->_arrayMap);
    }
}
