<?php

namespace LemoGrid\Adapter\Php;

use DateTime;
use LemoGrid\Adapter\AbstractAdapter;
use LemoGrid\Column\ColumnInterface;
use LemoGrid\Column\Concat as ColumnConcat;
use LemoGrid\Exception;
use LemoGrid\Event\AdapterEvent;
use LemoGrid\Platform\AbstractPlatform;
use LemoGrid\Platform\JqGridPlatform as JqGridPlatform;

class ArrayAdapter extends AbstractAdapter
{
    /**
     * @var array
     */
    protected $dataFiltered = array();

    /**
     * @var array
     */
    protected $dataSource = array();

    /**
     * @var array
     */
    protected $relations = array();

    /**
     * Constuctor
     *
     * @param array $dataSource Data as key => value or only values
     * @param array $relations Relation as relation alias => array field
     */
    public function __construct(array $dataSource = array(), array $relations = array())
    {
        $this->dataSource = $dataSource;
        $this->relations = $relations;
    }

    /**
     * Load data
     *
     * @return array
     */
    public function fetchData()
    {
        $rows = $this->getDataSource();
        $rowsCount = count($rows);
        $columns = $this->getGrid()->getIterator()->toArray();

        // Nacteme si kolekci dat
        $data = array();
        for ($indexRow = 0; $indexRow < $rowsCount; $indexRow++) {
            $item = $rows[$indexRow];

            foreach($columns as $indexCol => $column) {
                $colIdentifier = $column->getIdentifier();
                $colName = $column->getName();
                $data[$indexRow][$colName] = null;

                // Can we render value?
                if (true === $column->isValid($this, $item)) {

                    // Nacteme si data radku
                    $value = $this->findValue($colIdentifier, $item);

                    // COLUMN - DateTime
                    if ($value instanceof DateTime) {
                        $value = $value->format('Y-m-d H:i:s');
                    }

                    $column->setValue($value);

                    $value = $column->renderValue($this, $item);

                    // Projdeme data a nahradime data ve formatu %xxx%
                    if (null !== preg_match_all('/%(_?[a-zA-Z0-9\._-]+)%/', $value, $matches)) {
                        foreach ($matches[0] as $key => $match) {
                            if ('%_index%' == $matches[0][$key]) {
                                $value = str_replace($matches[0][$key], $indexRow, $value);
                            } else {
                                $value = str_replace($matches[0][$key], $this->findValue($matches[1][$key], $item), $value);
                            }
                        }
                    }

                    $data[$indexRow][$colName] = $value;
                    $column->setValue($value);
                }
            }
        }

        // Modify collection
        $data = $this->applyFilters($data);
        $data = $this->applySortings($data);

        // Set total count of items
        $this->countItemsTotal = count($data);
        $this->dataFiltered = $data;

        // Paginate collection
        $data = $this->applyPagination($data);

        // Set count of items
        $this->countItems = count($data);

        $this->getGrid()->getPlatform()->getResultSet()->setData($data);

        // Fetch summary data
        $this->fetchDataSummary();

        $event = new AdapterEvent();
        $event->setAdapter($this);
        $event->setGrid($this->getGrid());
        $event->setResultSet($this->getGrid()->getPlatform()->getResultSet());

        $this->getGrid()->getEventManager()->trigger(AdapterEvent::EVENT_FETCH_DATA, $this, $event);

        return $this;
    }

    /**
     * @return ArrayAdapter
     */
    protected function fetchDataSummary()
    {
        if ($this->getGrid()->getPlatform() instanceof JqGridPlatform && true === $this->getGrid()->getPlatform()->getOptions()->getUserDataOnFooter()) {
            $items = $this->dataFiltered;
            $itemsCount = count($items);

            // Find columns data for summary
            $columnsValues = array();
            for ($indexItem = 0; $indexItem < $itemsCount; $indexItem++) {
                $item = $items[$indexItem];

                foreach ($this->getGrid()->getColumns() as $indexCol => $column) {
                    $colName = $column->getName();

                    // Can we render value?
                    if (null !== $column->getAttributes()->getSummaryType() && true === $column->isValid($this, $item)) {
                        $columnsValues[$colName][$indexItem] = $item[$colName];
                    }
                }
            }

            // Calculate user data (SummaryRow)
            $dataUser = array();
            foreach ($this->getGrid()->getColumns() as $indexCol => $column) {

                // Sloupec je skryty, takze ho preskocime
                if (true === $column->getAttributes()->getIsHidden()) {
                    continue;
                }

                // Sloupec je skryty, musime ho preskocit
                if (true === $column->getAttributes()->getIsHidden()) {
                    continue;
                }

                if (null !== $column->getAttributes()->getSummaryType()) {
                    $colName = $column->getName();
                    $dataUser[$colName] = '';
                    $summaryType = $column->getAttributes()->getSummaryType();

                    if (isset($columnsValues[$colName])) {
                        if ('sum' == $summaryType) {
                            $dataUser[$colName] = array_sum($columnsValues[$colName]);
                        }
                        if ('min' == $summaryType) {
                            $dataUser[$colName] = min($columnsValues[$colName]);
                        }
                        if ('max' == $summaryType) {
                            $dataUser[$colName] = max($columnsValues[$colName]);
                        }
                        if ('count' == $summaryType) {
                            $dataUser[$colName] = array_sum($columnsValues[$colName]) / count($columnsValues[$colName]);
                        }
                    }
                }
            }

            $this->getGrid()->getPlatform()->getResultSet()->setDataUser($dataUser);
        }

        return $this;
    }

    /**
     * Apply filters to the collection
     *
     * @param  array $rows
     * @return array
     */
    protected function applyFilters(array $rows)
    {
        $grid = $this->getGrid();
        $filter = $grid->getParam('filters');

        if (empty($rows) || empty($filter['rules'])) {
            return $rows;
        }

        $columns = $this->getGrid()->getColumns();

        foreach ($rows as $indexRow => $item) {

            if (!empty($columns)) {
                foreach ($columns as $indexCol => $column) {

                    // Ma sloupec povolene vyhledavani?
                    if (true === $column->getAttributes()->getIsSearchable() && true !== $column->getAttributes()->getIsHidden()) {

                        // Jsou definovane filtry pro sloupec
                        if (!empty($filter['rules'][$column->getName()])) {
                            foreach ($filter['rules'][$column->getName()] as $filterDefinition) {
                                if ($column instanceof ColumnConcat) {
                                    preg_match('/' . $filterDefinition['value'] . '/i', $item[$column->getName()], $matches);

                                    if (count($matches) == 0) {
                                        unset($rows[$indexRow]);
                                    }
                                } else {
                                    if (false === $this->buildWhereFromFilter($column, $filterDefinition, $item[$column->getName()])) {
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
     * Apply pagination to the collection
     *
     * @param  array $rows
     * @return array
     */
    protected function applyPagination(array $rows)
    {
        $numberCurrentPage = $this->getGrid()->getPlatform()->getNumberOfCurrentPage();
        $numberVisibleRows = $this->getGrid()->getPlatform()->getNumberOfVisibleRows();

        // Strankovani
        if ($numberVisibleRows > 0) {
            $rows = array_slice($rows, $numberVisibleRows * $numberCurrentPage - $numberVisibleRows, $numberVisibleRows);
        }

        return $rows;
    }

    /**
     * Apply sorting to the collection
     *
     * @param  array $rows
     * @return array
     */
    protected function applySortings($rows)
    {
        $grid = $this->getGrid();
        $sort = $this->getGrid()->getPlatform()->getSort();

        if (empty($rows) || empty($sort)) {
            return $rows;
        }

        // Obtain a list of column
        foreach ($rows as $indexRow => $column) {
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
     * @param  array $dataSource
     * @return ArrayAdapter
     */
    public function setDataSource(array $dataSource)
    {
        $this->dataSource = $dataSource;
        return $this;
    }

    /**
     * Get data source array
     *
     * @return array|null
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * @param  array $relations
     * @return ArrayAdapter
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
