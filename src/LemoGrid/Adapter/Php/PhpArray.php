<?php

namespace LemoGrid\Adapter\Php;

use DateTime;
use LemoGrid\Adapter\AbstractAdapter;
use LemoGrid\Column\AbstractColumn;
use LemoGrid\Column\Concat as ColumnConcat;
use LemoGrid\Column\ConcatGroup as ColumnConcatGroup;
use LemoGrid\Exception;
use LemoGrid\Platform\AbstractPlatform;
use LemoGrid\ResultSet\JqGrid;

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
        $numberCurrentPage = $grid->getPlatform()->getNumberOfCurrentPage();
        $numberVisibleRows = $grid->getPlatform()->getNumberOfVisibleRows();

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
                if(null !== preg_match_all('/%(_?[a-zA-Z0-9\._-]+)%/', $value, $matches)) {
                    foreach($matches[0] as $key => $match) {
                        if ('%_index%' == $matches[0][$key]) {
                            $value = str_replace($matches[0][$key], $indexRow, $value);
                        } else {
                            $value = str_replace($matches[0][$key], $this->findValue($matches[1][$key], $item), $value);
                        }
                    }
                }

                if (null !== $column->getAttributes()->getSummaryType()) {
                    $dataSum[$colName][] = $value;
                }

                $data[$colName] = $value;
                $column->setValue($value);
            }

            $collection[] = $data;
        }

        $collection = $this->_filterCollection($collection);
        $this->countItemsTotal = count($collection);
        $this->countItems = count($collection);

        $collection = $this->_sortCollection($collection);
        $collection = array_slice($collection, $numberVisibleRows * $numberCurrentPage - $numberVisibleRows, $numberVisibleRows);

        $this->setResultSet(new JqGrid($collection));
        unset($collection);

        // Calculate user data (SummaryRow)
        if (isset($dataSum)) {
            foreach ($this->getGrid()->getColumns() as $column) {
                if (null !== $column->getAttributes()->getSummaryType()) {
                    $colName = $column->getName();
                    $summaryType = $column->getAttributes()->getSummaryType();

                    if ('sum' == $summaryType) {
                        $summaryData[$colName] = array_sum($dataSum[$colName]);
                    }
                    if ('min' == $summaryType) {
                        $summaryData[$colName] = min($dataSum[$colName]);
                    }
                    if ('max' == $summaryType) {
                        $summaryData[$colName] = max($dataSum[$colName]);
                    }
                    if ('count' == $summaryType) {
                        $summaryData[$colName] = array_sum($dataSum[$colName]) / count($dataSum[$colName]);
                    }
                }
            }

            $this->getResultSet()->setUserData($summaryData);

            unset($dataSum);
        }

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

                        if($col instanceof ColumnConcat || $col instanceof ColumnConcatGroup) {
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
                            if(false === $this->addWhereFromFilter($col, $col->getAttributes()->getFormat(), $filters[$col->getName()], $item[$col->getName()])) {
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
        $sort = $this->getGrid()->getPlatform()->getSort();

        if(empty($collection) || empty($sort)) {
            return $collection;
        }

        // Obtain a list of columns
        foreach ($collection as $index => $column) {
            $keys = array_keys($column);

            foreach ($keys as $key) {
                $parts[$key][$index] = $column[$key];
            }
        }

        $arguments = array();
        foreach ($sort as $sortColumn => $sortDirect) {
            $arguments[] = $parts[$sortColumn];
            $arguments[] = ('asc' == $sortDirect) ? SORT_ASC : SORT_DESC;
            $arguments[] = SORT_REGULAR;
        }
        $arguments[] = &$collection;

        call_user_func_array('array_multisort', $arguments);

        return $collection;
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
        $identifier = str_replace('_', '.', $identifier);
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
     * @param  AbstractColumn $column
     * @param  string         $format
     * @param  array          $filter
     * @param  string         $value
     * @return bool
     * @throws Exception\InvalidArgumentException
     */
    protected function addWhereFromFilter($column, $format, $filter, $value)
    {
        $isValid = true;
        $valueFilter = $filter['value'];

        // Pravedeme neuplny string na DbDate
        if ('date' == $format) {
            $valueFilter = $this->convertLocaleDateToDbDate($valueFilter);
        }

        switch ($filter['operator']) {
            case AbstractPlatform::OPERATOR_EQUAL:
                if ($value != $valueFilter) {
                    $isValid = false;
                }
                break;
            case AbstractPlatform::OPERATOR_NOT_EQUAL:
                if ($value == $valueFilter) {
                    $isValid = false;
                }
                break;
            case AbstractPlatform::OPERATOR_LESS:
                if ($value >= $valueFilter) {
                    $isValid = false;
                }
                break;
            case AbstractPlatform::OPERATOR_LESS_OR_EQUAL:
                if ($value > $valueFilter) {
                    $isValid = false;
                }
                break;
            case AbstractPlatform::OPERATOR_GREATER:
                if ($value <= $valueFilter) {
                    $isValid = false;
                }
                break;
            case AbstractPlatform::OPERATOR_GREATER_OR_EQUAL:
                if ($value < $valueFilter) {
                    $isValid = false;
                }
                break;
            case AbstractPlatform::OPERATOR_BEGINS_WITH:
                $count = preg_match('/^' . $valueFilter . '/i', $value, $matches);
                if ($count == 0) {
                    $isValid = false;
                }
                break;
            case AbstractPlatform::OPERATOR_NOT_BEGINS_WITH:
                $count = preg_match('/^' . $valueFilter . '/i', $value, $matches);
                if ($count > 0) {
                    $isValid = false;
                }
                break;
            case AbstractPlatform::OPERATOR_IN:
                break;
            case AbstractPlatform::OPERATOR_NOT_IN:
                break;
            case AbstractPlatform::OPERATOR_ENDS_WITH:
                $count = preg_match('/' . $valueFilter . '$/i', $value, $matches);
                if ($count == 0) {
                    $isValid = false;
                }
                break;
            case AbstractPlatform::OPERATOR_NOT_ENDS_WITH:
                $count = preg_match('/' . $valueFilter . '$/i', $value, $matches);
                if ($count > 0) {
                    $isValid = false;
                }
                break;
            case AbstractPlatform::OPERATOR_CONTAINS:
                $count = preg_match('/' . $valueFilter . '/i', $value, $matches);
                if ($count == 0) {
                    $isValid = false;
                }
                break;
            case AbstractPlatform::OPERATOR_NOT_CONTAINS:
                $count = preg_match('/' . $valueFilter . '/i', $value, $matches);
                if ($count > 0) {
                    $isValid = false;
                }
                break;
            default:
                throw new Exception\InvalidArgumentException('Invalid filter operator');
        }

        return $isValid;
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
