<?php

namespace LemoGrid\Adapter\Php;

use DateTime;
use LemoGrid\Adapter\AbstractAdapter;
use LemoGrid\Column\Concat as ColumnConcat;
use LemoGrid\Column\ConcatGroup as ColumnConcatGroup;
use LemoGrid\ResultSet\Data;

class PhpArray extends AbstractAdapter
{
    /**
     * @var array
     */
    protected $rawData = array();

    /**
     * @var array
     */
    protected $relations = array();

    /**
     * Constuctor
     *
     * @param array $rawData   Data as key => value or only values
     * @param array $relations Relation as relation alias => array field
     */
    public function __construct(array $rawData = array(), array $relations = array())
    {
        $this->rawData = $rawData;
        $this->relations = $relations;
    }

    /**
     * Load data
     *
     * @return array
     */
    public function populateData()
    {
        $grid = $this->getGrid();
        $collection = array();

        foreach($this->getRawData() as $indexRow => $item)
        {
            $data = array();

            foreach($grid->getColumns() as $column) {
                $colIdentifier = $column->getIdentifier();
                $colName = $column->getName();
                $data[$colName] = null;

                // Nacteme si data radku
                $value = $this->findValue($colIdentifier, $item);
                $column->setValue($value);

                $value = $column->renderValue();

                if (null === $value) {
                    continue;
                }

                // COLUMN - DateTime
                if($value instanceof DateTime) {
                    $value = $value->format('Y-m-d H:i:s');
                }

                // COLUMN - Concat
                if($column instanceof ColumnConcat) {
                    $value = null;
                    $values = array();
                    foreach($column->getOptions()->getIdentifiers() as $identifier) {
                        $val = $this->findValue($identifier, $item);

                        if(!empty($val)) {
                            if($val instanceof DateTime) {
                                $val = $value->format('Y-m-d H:i:s');
                            }

                            $values[] = $val;
                        }
                    }

                    $patternCount = count($values);
                    $patternCountParts = substr_count($column->getOptions()->getPattern(), '%s');
                    if ($patternCount > 0 && $patternCount == $patternCountParts) {
                        $value = vsprintf($column->getOptions()->getPattern(), $values);
                    }

                    unset($values, $identifier);
                }

                // COLUMN - Concat group
                if($column instanceof ColumnConcatGroup) {
                    $value = null;
                    $values = array();

                    $valuesLine = array();
                    foreach($column->getOptions()->getIdentifiers() as $identifier) {
                        $val = $this->findValue($identifier, $item);

                        if (null !== $val) {
                            foreach ($val as $index => $v) {
                                if($v instanceof DateTime) {
                                    $v = $v->format('Y-m-d H:i:s');
                                }

                                $valuesLine[$index][] = $v;
                            }
                        }
                    }

                    // Slozime jednotlive casti na radak
                    foreach ($valuesLine as $line) {
                        $patternCount = count($line);
                        $patternCountParts = substr_count($column->getOptions()->getPattern(), '%s');
                        if ($patternCount > 0 && $patternCount == $patternCountParts) {
                            $values[] = vsprintf($column->getOptions()->getPattern(), $line);
                        } else {
                            $values[] = null;
                        }
                    }

                    $value = implode($column->getOptions()->getSeparator(), $values);

                    unset($values, $valuesLine, $identifier);
                }

                // Projdeme data a nahradime data ve formatu %xxx%
                if(preg_match_all('/%(_?[a-zA-Z0-9\._-]+)%/', $value, $matches)) {
                    foreach($matches[0] as $key => $match) {
                        if ('%_index%' == $matches[0][$key]) {
                            $value = str_replace($matches[0][$key], $indexRow, $value);
                        } else {
                            $value = str_replace($matches[0][$key], $this->findValue($matches[1][$key], $item), $value);
                        }
                    }
                }

                $data[$colName] = $value;
                $column->setValue($value);
            }

            $collection[] = $data;
        }

        $this->countItemsTotal = count($collection);
        $collection = $this->_filterCollection($collection);
        $this->countItems = count($collection);

        $collection = $this->_sortCollection($collection);
        $collection = array_slice($collection, $this->getNumberOfVisibleRows() * $this->getNumberOfCurrentPage() - $this->getNumberOfVisibleRows(), $this->getNumberOfVisibleRows());

        $this->setData(new Data($collection));

        return $this;
    }

    /**
     * Filtr collection
     *
     * @param  array $collection
     * @return array
     */
    private function _filterCollection(array $collection)
    {
        $grid = $this->getGrid();
        $filters = $grid->getParam('filters');

        if(empty($collection) || empty($filters)) {
            return $collection;
        }

        foreach($collection as $index => $item)
        {
            foreach($grid->getColumns() as $col)
            {
                if($col->getAttributes()->getIsSearchable()) {
                    $prepend = null;
                    $append = null;

                    if(array_key_exists($col->getName(), $filters)) {

                        if($col instanceof ColumnConcat) {
                            $isValid = true;
                            foreach($col->getOptions()->getIdentifiers() as $identifier){
                                if(!preg_match('/' . $filters[$col->getName()]['value'] . '/i', $item[$identifier])) {
                                    $isValid == false;
                                }
                            }
                            if (false === $isValid) {
                                unset($collection[$index]);
                            }
                        } else {
                            if(!preg_match('/' . $filters[$col->getName()]['value'] . '/i', $item[$col->getIdentifier()])) {
                                unset($collection[$index]);
                            }
                        }
                    }
                }
            }
        }

        return $collection;
    }

    /**
     * Sort collection
     *
     * @param  array $collection
     * @return array
     */
    private function _sortCollection($collection)
    {
        $dataSorted = array();
        $grid = $this->getGrid();

        if($collection == null) {
            return $collection;
        }

        if($grid->has($grid->getPlatform()->getSortColumn())) {
            if($grid->get($grid->getPlatform()->getSortColumn()) instanceof ColumnConcat) {
                foreach($grid->get($grid->getPlatform()->getSortColumn())->getOptions()->getIdentifiers() as $identifier){
                    $sortColumn = $identifier;
                    $sortDirect = $grid->getPlatform()->getSortDirect();
                }
            } else {
                $sortColumn = $grid->get($grid->getPlatform()->getSortColumn())->getIdentifier();
                $sortDirect = $grid->getPlatform()->getSortDirect();
            }
        }

        if (!isset($sortColumn)) {
            foreach($grid->getColumns() as $col) {
                if($col->getAttributes()->getIsSortable()) {
                    $sortColumn = $col->getIdentifier();
                    $sortDirect = 'asc';
                    break;
                }
            }
        }

        if (empty($sortColumn) || empty($sortDirect)) {
            return $collection;
        }

        foreach(array_keys($collection) as $key) {
            $temp[$key] = $collection[$key];

            if(strtolower($sortDirect) == 'asc') {
                asort($temp);
            } else {
                arsort($temp);
            }
        }

        foreach(array_keys($temp) as $key) {
            if(is_numeric($key)) {
                $dataSorted[] = $collection[$key];
            } else {
                $dataSorted[$key] = $collection[$key];
            }
        }

        return $dataSorted;
    }

    /**
     * Find value for column
     *
     * @param  string $identifier
     * @param  array  $item
     * @return null|string
     */
    protected function findValue($identifier, array $item)
    {
        // Determinate column name and alias name
        $identifier = substr($identifier, strpos($identifier, '.') +1);
        $parts = explode('.', $identifier);

        if (isset($item[$parts[0]]) && count($parts) > 1) {
            return $this->findValue($identifier, $item[$parts[0]]);
        }

        if (isset($item[$identifier])) {
            return $item[$identifier];
        } else {
            if (isset($item[0])) {

                $return = array();
                foreach ($item as $it) {
                    if (isset($it[$identifier])) {
                        $return[] = $it[$identifier];
                    }
                }

                return $return;
            }
        }

        return null;
    }

    /**
     * @param  array $rawData
     * @return PhpArray
     */
    public function setRawData(array $rawData)
    {
        $this->rawData = $rawData;
        return $this;
    }

    /**
     * Get data source array
     *
     * @return array|null
     */
    public function getRawData()
    {
        return $this->rawData;
    }

    /**
     * @param  array $relations
     * @return PhpArray
     */
    public function setRelations(array $relations)
    {
        $this->relations = $relations;
        return $this;
    }

    /**
     * @return array
     */
    public function getRelations()
    {
        return $this->relations;
    }
}
