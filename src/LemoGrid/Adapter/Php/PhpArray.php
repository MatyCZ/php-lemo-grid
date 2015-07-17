<?php

namespace LemoGrid\Adapter\Php;

use DateTime;
use LemoGrid\Adapter\AbstractAdapter;
use LemoGrid\Column\AbstractColumn;
use LemoGrid\Column\ColumnInterface;
use LemoGrid\Column\Concat as ColumnConcat;
use LemoGrid\Column\ConcatGroup as ColumnConcatGroup;
use LemoGrid\Exception;
use LemoGrid\Event\AdapterEvent;
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
     * Return adapter name
     *
     * @return string
     */
    public function getName()
    {
        return 'php_array';
    }

    /**
     * Load data
     *
     * @return array
     */
    public function populateData()
    {
        $grid = $this->getGrid();
        $numberCurrentPage = $grid->getPlatform()->getNumberOfCurrentPage();
        $numberVisibleRows = $grid->getPlatform()->getNumberOfVisibleRows();

        $rows = $this->getRawData();
        $columns = $this->getGrid()->getIterator()->toArray();

        // Nacteme si kolekci dat
        $collection = array();
        foreach($rows as $indexRow => $item) {
            $data = array();

            foreach($columns as $indexCol => $column) {
                $colIdentifier = $column->getIdentifier();
                $colName = $column->getName();
                $data[$colName] = null;

                // Can we render value?
                if (true === $column->isValid($this, $item)) {

                    // Nacteme si data radku
                    $value = $this->findValue($colIdentifier, $item);

                    // COLUMN - DateTime
                    if($value instanceof DateTime) {
                        $value = $value->format('Y-m-d H:i:s');
                    }

                    $column->setValue($value);

                    $value = $column->renderValue($this, $item);

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

                    $data[$colName] = $value;
                    $column->setValue($value);
                }
            }

            $collection[] = $data;
        }


        // Zafiltrujeme kolekci
        $collection = $this->_filterCollection($collection);

        // Seradime kolekci kolekci
        $collection = $this->_sortCollection($collection);

        // Spocteme si pocet polozek
        $this->countItems = count($collection);
        $this->countItemsTotal = count($collection);

        // Najdeme si data pro souctovy radek
        $dataSum = array();
        if (!empty($collection)) {
            foreach($collection as $indexRow => $item) {
                foreach($columns as $indexCol => $column) {
                    $colName = $column->getName();

                    if (null !== $column->getAttributes()->getSummaryType()) {
                        $dataSum[$colName][$indexRow] = $item[$colName];
                    }
                }
            }
        }

        // Strankovani
        if ($numberVisibleRows > 0) {
            $collection = array_slice($collection, $numberVisibleRows * $numberCurrentPage - $numberVisibleRows, $numberVisibleRows);
        }

        $this->setResultSet(new JqGrid($collection));

        // Calculate user data (SummaryRow)
        $summaryData = array();
        foreach($columns as $indexCol => $column) {

            // Sloupec je skryty, takze ho preskocime
            if (true === $column->getAttributes()->getIsHidden()) {
                continue;
            }

            if (null !== $column->getAttributes()->getSummaryType()) {
                $colName = $column->getName();
                $summaryData[$colName] = '';
                $summaryType = $column->getAttributes()->getSummaryType();

                if (isset($dataSum[$colName])) {
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
        }

        $this->getResultSet()->setUserData($summaryData);

        $event = new AdapterEvent();
        $event->setAdapter($this);
        $event->setAdapterName($this->getName());
        $event->setData($collection);
        $event->setGridName($this->getGrid()->getName());

        $grid->getEventManager()->trigger(AdapterEvent::EVENT_LOAD_DATA, $this, $event);

        return $this;
    }

    /**
     * Filtr collection
     *
     * @param  array $rows
     * @return array
     */
    private function _filterCollection(array $rows)
    {
        $grid = $this->getGrid();
        $filter = $grid->getParam('filters');

        if(empty($rows) || empty($filter['rules'])) {
            return $rows;
        }

        $columns = $this->getGrid()->getColumns();

        foreach($rows as $indexRow => $item) {

            if (!empty($columns)) {
                foreach($columns as $indexCol => $column) {

                    // Ma sloupec povolene vyhledavani?
                    if($column->getAttributes()->getIsSearchable() && true !== $column->getAttributes()->getIsHidden()) {

                        // Jsou definovane filtry pro sloupec
                        if(!empty($filter['rules'][$column->getName()])) {
                            foreach ($filter['rules'][$column->getName()] as $filterDefinition) {
                                if($column instanceof ColumnConcat || $column instanceof ColumnConcatGroup) {
                                    preg_match('/' . $filterDefinition['value'] . '/i', $item[$column->getName()], $matches);

                                    if (count($matches) == 0) {
                                        unset($rows[$indexRow]);
                                    }
                                } else {
                                    if(false === $this->buildWhereFromFilter($column, $filterDefinition, $item[$column->getName()])) {
                                        unset($rows[$indexRow]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $rows;
    }

    /**
     * Sort collection
     *
     * @param  array $rows
     * @return array
     */
    private function _sortCollection($rows)
    {
        $grid = $this->getGrid();
        $sort = $this->getGrid()->getPlatform()->getSort();

        if(empty($rows) || empty($sort)) {
            return $rows;
        }

        // Obtain a list of column
        foreach($rows as $indexRow => $column) {

            $keys = array_keys($column);

            foreach ($keys as $key) {
                $parts[$key][$indexRow] = $column[$key];
            }
        }

        $arguments = array();
        foreach ($sort as $sortColumn => $sortDirect) {
            if ($grid->has($sortColumn)) {
                if (false !== $grid->get($sortColumn)->getAttributes()->getIsSortable() && true !== $grid->get($sortColumn)->getAttributes()->getIsHidden()) {
                    $arguments[] = $parts[$sortColumn];
                    $arguments[] = ('asc' == $sortDirect) ? SORT_ASC : SORT_DESC;
                    $arguments[] = SORT_REGULAR;
                }
            }
        }
        $arguments[] = & $rows;

        call_user_func_array('array_multisort', $arguments);

        return $rows;
    }

    /**
     * Find value for column
     *
     * @param  string $identifier
     * @param  array  $item
     * @param  int    $depth
     * @return null|string
     */
    public function findValue($identifier, array $item, $depth = 0)
    {
        // Determinate column name and alias name
        $identifier = str_replace('_', '.', $identifier);

        if (false !== strpos($identifier, '.')) {
            $identifier = substr($identifier, strpos($identifier, '.') +1);
        }

        $parts = explode('.', $identifier);
        if (isset($item[$parts[0]]) && count($parts) > 1) {
            return $this->findValue($identifier, $item[$parts[0]], $depth+1);
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
     * @param  ColumnInterface $column
     * @param  array           $filterDefinition
     * @param  string          $value
     * @return bool
     * @throws Exception\InvalidArgumentException
     */
    protected function buildWhereFromFilter(ColumnInterface $column, $filterDefinition, $value)
    {
        $isValid = true;
        $operator = $filterDefinition['operator'];
        $valueFilter = $filterDefinition['value'];

        // Pravedeme neuplny string na DbDate
        if ('date' == $column->getAttributes()->getFormat()) {
            $valueFilter = $this->convertLocaleDateToDbDate($valueFilter);
        }

        switch ($operator) {
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
