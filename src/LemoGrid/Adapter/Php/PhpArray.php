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

        foreach($this->getRawData() as $item)
        {
            $data = array();

            foreach($grid->getColumns() as $column) {
                $colIdentifier = $column->getIdentifier();
                $data[$colIdentifier] = null;

                // Nacteme si data radku
                $value = $this->findValueByRowData($colIdentifier, $item);
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
                    $values = array();
                    foreach($column->getOptions()->getIdentifiers() as $identifier) {
                        $val = $this->findValueByColumnData($identifier, $column->getValue());

                        if(!empty($val)) {
                            if($val instanceof DateTime) {
                                $val = $value->format('Y-m-d H:i:s');
                            }

                            $values[] = $val;
                        }
                    }

                    $value = vsprintf($column->getOptions()->getPattern(), $values);

                    unset($values, $identifier);
                }

                // COLUMN - Concat group
                if($column instanceof ColumnConcatGroup) {
                    $values = array();

                    if (is_array($column->getValue())) {
                        foreach ($column->getValue() as $value) {

                            $valuesLine = array();
                            foreach($column->getOptions()->getIdentifiers() as $identifier) {
                                $val = $this->findValueByColumnData($identifier, $value);

                                if(!empty($val)) {
                                    if($val instanceof DateTime) {
                                        $val = $value->format('Y-m-d H:i:s');
                                    }

                                    $valuesLine[] = $val;
                                }
                            }

                            $values[] = vsprintf($column->getOptions()->getPattern(), $valuesLine);
                        }
                    }

                    $value = implode($column->getOptions()->getSeparator(), $values);

                    unset($values, $valuesLine, $identifier);
                }

                // Projdeme data a nahradime data ve formatu %xxx%
                if(preg_match_all('/%([a-zA-Z0-9\._-]+)%/', $value, $matches)) {
                    foreach($matches[0] as $key => $match) {
                        $value = str_replace($matches[0][$key], $this->findValueByRowData($matches[1][$key], $item), $value);
                    }
                }

                $data[$colIdentifier] = $value;
                $column->setValue($value);
            }

            $collection[] = $data;
        }

        $this->countItemsTotal = count($collection);
        $collection = $this->_filterCollection($collection);
        $this->countItems = count($collection);

        $collection = $this->_sortCollection($collection);
        $collection = array_slice($collection, $grid->getPlatform()->getOptions()->getRecordsPerPage() * $this->getNumberOfCurrentPage() - $grid->getPlatform()->getOptions()->getRecordsPerPage(), $grid->getPlatform()->getOptions()->getRecordsPerPage());

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
     * @param  string $columnName
     * @param  array  $item
     * @return null|string
     */
    protected function findValueByRowData($columnName, array $item)
    {
        // Determinate column name and alias name
        $explode = explode('.', $columnName);

        if(isset($explode[1])) {
            $name = $explode[1];
            $relationAlias = $explode[0];
        } else {
            $name = $explode[0];
            $relationAlias = null;
        }

        // Try find item in root
        if(array_key_exists($name, $item) && (null === $relationAlias || !array_key_exists($relationAlias, $this->relations))) {
            return $item[$name];
        }

        // Try find item in relations
        if(array_key_exists($relationAlias, $this->relations)) {
            $relation = explode('/', $this->relations[$relationAlias]);
            $itemRelation = $item;

            // Read data from relation
            $founded = false;
            foreach ($relation as $rel) {
                if(isset($itemRelation[$rel][0]) && count($itemRelation[$rel]) == 1) {
                    $itemRelation = $itemRelation[$rel][0];
                    $founded = true;
                } elseif(isset($itemRelation[$rel])) {
                    $itemRelation = $itemRelation[$rel];
                    $founded = true;
                }
            }

            if(true === $founded) {
                return $itemRelation[$name];
            } else {
                return null;
            }
        }

        return null;
    }

    /**
     * Find value for column
     *
     * @param  string $identifier
     * @param  array $value
     * @return null|string
     */
    protected function findValueByColumnData($identifier, $value)
    {
        // Determinate column name and alias name
        $explode = explode('.', $identifier);

        if(isset($explode[1])) {
            $name = $explode[1];
        } else {
            $name = $explode[0];
        }

        // Try find item in root
        if (is_array($value)) {
            if(array_key_exists($name, $value)) {
                return $value[$name];
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
