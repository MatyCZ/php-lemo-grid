<?php

namespace LemoGrid\Adapter\Zend;

use DateTime;
use LemoGrid\Adapter\AbstractAdapter;
use LemoGrid\Column\ColumnInterface;
use LemoGrid\Column\Concat as ColumnConcat;
use LemoGrid\Event\AdapterEvent;
use LemoGrid\Exception;
use LemoGrid\GridInterface;
use LemoGrid\Platform\AbstractPlatform;
use LemoGrid\Platform\JqGridPlatform as JqGridPlatform;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class SelectAdapter extends AbstractAdapter
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var Select
     */
    protected $select = null;

    /**
     * @throws Exception\UnexpectedValueException
     * @return $this
     */
    public function fetchData()
    {
        if (!$this->getGrid() instanceof GridInterface) {
            throw new Exception\UnexpectedValueException("No Grid instance given");
        }
        if (!$this->getSelect() instanceof Select) {
            throw new Exception\UnexpectedValueException(sprintf("No '%s' instance given", Select::class));
        }

        // Modify DQL
        $this->applyFilters();
        $this->applyPagination();
        $this->applySortings();


//        $sql = new Sql($this->getAdapter());
//        \Zend\Debug\Debug::dump($sql->getSqlStringForSqlObject($this->getSelect()));
//        exit;

        $paginatorAdapter = new DbSelect($this->getSelect(), $this->getAdapter());
        $paginator = new Paginator($paginatorAdapter);
        $paginator->setCurrentPageNumber($this->getGrid()->getPlatform()->getNumberOfCurrentPage());
        $paginator->setItemCountPerPage($this->getGrid()->getPlatform()->getNumberOfVisibleRows());

        $columns = $this->getGrid()->getIterator()->toArray();
        $rows = $paginator->getIterator()->getArrayCopy();
        $rowsCount = $paginator->getCurrentItemCount();

        // Update count of items
        $this->countItems = $rowsCount;
        $this->countItemsTotal = $paginator->getTotalItemCount();

        $data = array();
        for ($indexRow = 0; $indexRow < $rowsCount; $indexRow++) {
            $item = (array) $rows[$indexRow];

            foreach ($columns as $indexCol => $column) {
                $colIdentifier = $column->getIdentifier();
                $colName = $column->getName();
                $data[$indexRow][$colName] = null;

                // Can we render value?
                if (true === $column->isValid($this, $item)) {

                    // Nacteme si data radku
                    $value = $this->findValue($colName, $item);

                    // COLUMN - DateTime
                    if ($value instanceof DateTime) {
                        $value = $value->format('Y-m-d H:i:s');
                    }

                    $column->setValue($value);

                    $value = $column->renderValue($this, $item);

                    // Projdeme data a nahradime data ve formatu %xxx%
                    if (null !== $value && preg_match_all('/%(_?[a-zA-Z0-9\._-]+)%/', $value, $matches)) {
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

        $this->getGrid()->getPlatform()->getResultSet()->setData($data);

        // Fetch summary data
        $this->fetchDataSummary();

        $event = new AdapterEvent();
        $event->setAdapter($this);
        $event->setGrid($this->getGrid());
        $event->setResultSet($this->getGrid()->getPlatform()->getResultSet());

        $this->getGrid()->getEventManager()->trigger(AdapterEvent::EVENT_FETCH_DATA, $this, $event);

        $this->getGrid()->setAdapter($event->getAdapter());
        $this->getGrid()->getPlatform()->setResultSet($event->getResultSet());

        return $this;
    }

    /**
     * @return $this
     */
    protected function fetchDataSummary()
    {
//        if ($this->getGrid()->getPlatform() instanceof JqGridPlatform && true === $this->getGrid()->getPlatform()->getOptions()->getUserDataOnFooter()) {
//            $select = $this->getSelect();
//            $select->reset('select');
//            $select->reset('order');
//            $select->offset(null);
//            $select->limit(null);
//
//            // Add group by
//            $rootAliases = $select->getRootAliases();
//            $rootEntities = $select->getRootEntities();
//
//            $identifiers = $this->getSelect()
//                ->getEntityManager()
//                ->getClassMetadata($rootEntities[0])
//                ->getIdentifierFieldNames();
//
//            foreach ($identifiers as $identifier) {
//                $select->addGroupBy($rootAliases[0] . '.' . $identifier);
//            }
//
//            $summary = [];
//            $countOfSummaryColumn = 0;
//            foreach ($this->getGrid()->getColumns() as $indexCol => $column) {
//                $columnQuery = clone $select;
//
//                // Sloupec je skryty, takze ho preskocime
//                if (true === $column->getAttributes()->getIsHidden()) {
//                    continue;
//                }
//
//                if (null !== $column->getAttributes()->getSummaryType()) {
//                    $summaryType = $column->getAttributes()->getSummaryType();
//
//                    $columnQuery->addSelect($column->getIdentifier());
//
//                    $countOfSummaryColumn++;
//
//                    $values = array_map('current', $columnQuery->getQuery()->getScalarResult());
//
//                    switch ($summaryType) {
//                        case 'avg':
//                            $summary[$column->getName()] = array_sum($values) / count($values);
//                            break;
//                        case 'max':
//                            $summary[$column->getName()] = max($values);
//                            break;
//                        case 'min':
//                            $summary[$column->getName()] = min($values);
//                            break;
//                        case 'sum':
//                            $summary[$column->getName()] = array_sum($values);
//                            break;
//                    }
//                }
//            }
//
//            if (!empty($summary)) {
//                $this->getGrid()->getPlatform()->getResultSet()->setDataUser($summary);
//            }
//        }

        return $this;
    }

    /**
     * Apply filters to the Select
     *
     * @return $this
     */
    protected function applyFilters()
    {
        $columns = $this->getGrid()->getIterator()->toArray();
        $filter = $this->getGrid()->getParam('filters');

        // WHERE
        if (!empty($filter['rules'])) {

            $whereCol = array();
            foreach($columns as $indexCol => $col) {
                if (true === $col->getAttributes()->getIsSearchable() && true !== $col->getAttributes()->getIsHidden()) {

                    // Jsou definovane filtry pro sloupec
                    if(!empty($filter['rules'][$col->getName()])) {

                        $whereColSub = array();
                        foreach ($filter['rules'][$col->getName()] as $filterDefinition) {
                            if (in_array($filterDefinition['operator'], ['~', '!~'])) {

                                // Odstranime duplicity a prazdne hodnoty
                                $filterWords = [];
                                foreach (explode(' ', $filterDefinition['value']) as $word) {
                                    if (in_array($word, $filterWords)) {
                                        continue;
                                    }

                                    if (empty($word)) {
                                        continue;
                                    }

                                    $filterWords[] = $word;
                                }

                                if ($col instanceof ColumnConcat) {

                                    $concat = $this->buildConcat($col->getOptions()->getIdentifiers());

                                    $predicateColSub = new Predicate();
                                    foreach ($filterWords as $filterWord) {
                                        $predicate = $this->buildWhereFromFilter($col, $concat, array(
                                            'operator' => $filterDefinition['operator'],
                                            'value'    => $filterWord
                                        ));

                                        // Urcime pomoci jakeho operatoru mame skladat jednotlive vyrazi hledani sloupce
                                        if ('and' == $col->getAttributes()->getSearchGroupOperator()) {
                                            $predicateColSub->andPredicate($predicate);
                                        } else {
                                            if ('~' === $filterDefinition['operator']) {
                                                $predicateColSub->orPredicate($predicate);
                                            } else {
                                                $predicateColSub->andPredicate($predicate);
                                            }
                                        }
                                    }

                                    $whereColSub[] = $predicateColSub;
                                } else {

                                    $predicateColSub = new Predicate();
                                    foreach ($filterWords as $filterWord) {
                                        $predicate = $this->buildWhereFromFilter($col, $col->getIdentifier(), array(
                                            'operator' => $filterDefinition['operator'],
                                            'value'    => $filterWord,
                                        ));

                                        if ('and' == $col->getAttributes()->getSearchGroupOperator()) {
                                            $predicateColSub->andPredicate($predicate);
                                        } else {
                                            if ('~' === $filterDefinition['operator']) {
                                                $predicateColSub->orPredicate($predicate);
                                            } else {
                                                $predicateColSub->andPredicate($predicate);
                                            }
                                        }
                                    }

                                    $whereColSub[] = $predicateColSub;
                                }
                            } else {

                                // Sestavime filtr pro jednu podminku sloupce
                                $predicateColSub = new Predicate();
                                if ($col instanceof ColumnConcat) {
                                    foreach ($col->getOptions()->getIdentifiers() as $identifier) {
                                        $predicateColSub->orPredicate($this->buildWhereFromFilter($col, $identifier, $filterDefinition));
                                    }
                                } else {
                                    $predicateColSub->orPredicate($this->buildWhereFromFilter($col, $col->getIdentifier(), $filterDefinition));
                                }

                                // Sloucime podminky sloupce pomoci OR (z duvodu Concat sloupce)
                                $whereColSub[] = $predicateColSub;
                            }
                        }

                        // Urcime pomoci jako operatoru mame sloupcit jednotlive podminky
                        if (!empty($whereColSub)) {
                            if (count($whereColSub) == 1) {
                                $predicateCol = $whereColSub[0];
                            } else {
                                $predicateCol = new Predicate();
                                foreach ($whereColSub as $w) {
                                    if ('and' == $filter['operator']) {
                                        $predicateCol->andPredicate($w);
                                    } else {
                                        $predicateCol->orPredicate($w);
                                    }
                                }
                            }

                            $whereCol[] = $predicateCol;
                        }
                    }
                }
            }

            // Pridame k vychozimu WHERE i WHERE z filtrace sloupcu
            if (!empty($whereCol)) {
                if (count($whereCol) == 1) {
                    $predicate = $whereCol[0];
                } else {
                    $predicate = new Predicate();
                    foreach ($whereCol as $w) {
                        if ('and' == $filter['operator']) {
                            $predicate->andPredicate($w);
                        } else {
                            $predicate->orPredicate($w);
                        }
                    }
                }

                $this->getSelect()->where($predicate);
            }
        }

        return $this;
    }

    /**
     * Apply pagination to the Select
     *
     * @return $this
     */
    protected function applyPagination()
    {
        $numberCurrentPage = $this->getGrid()->getPlatform()->getNumberOfCurrentPage();
        $numberVisibleRows = $this->getGrid()->getPlatform()->getNumberOfVisibleRows();

        // Calculate offset
        if ($numberVisibleRows > 0) {
            $offset = $numberVisibleRows * $numberCurrentPage - $numberVisibleRows;
            if($offset < 0) {
                $offset = 0;
            }

            $this->getSelect()->limit((int) $numberVisibleRows);
            $this->getSelect()->offset((int) $offset);
        }

        return $this;
    }

    /**
     * Apply sorting to the QueryBuilder
     *
     * @return $this
     */
    protected function applySortings()
    {
        $sort = $this->getGrid()->getPlatform()->getSort();

        // Store default order to variable and reset orderBy
        $orderBy = $this->getSelect()->getRawState('order');
        $this->getSelect()->reset('order');

        // ORDER
        if (!empty($sort)) {
            foreach ($sort as $sortColumn => $sortDirect) {
                if ($this->getGrid()->has($sortColumn)) {
                    if (false !== $this->getGrid()->get($sortColumn)->getAttributes()->getIsSortable() && true !== $this->getGrid()->get($sortColumn)->getAttributes()->getIsHidden()) {
                        if ($this->getGrid()->get($sortColumn) instanceof ColumnConcat) {
                            foreach($this->getGrid()->get($sortColumn)->getOptions()->getIdentifiers() as $identifier){
                                $this->getSelect()->order($identifier . ' ASC');
                            }
                        } else {
                            $this->getSelect()->order($this->getGrid()->get($sortColumn)->getIdentifier() . ' ' . $sortDirect);
                        }
                    }
                }
            }
        }

        // Add default order from variable
        if (!empty($orderBy)) {
            foreach ($orderBy as $order) {
                $this->getSelect()->order($order);
            }
        }

        return $this;
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
     * Sestavi CONCAT z predanych casti
     *
     * @param  array  $identifiers
     * @return Expression
     */
    protected function buildConcat(array $identifiers)
    {
        if (count($identifiers) > 1) {
            $parts = [];
            foreach ($identifiers as $identifier) {
                $parts[] = "CASE WHEN  (" . $identifier . " IS NULL) THEN '' ELSE " . $identifier . " END";
            }

            return new Expression('CONCAT', $parts);
        }

        return reset($identifiers);
    }


    /**
     * @param  ColumnInterface $column
     * @param  string          $identifier
     * @param  array           $filterDefinition
     * @return Predicate
     * @throws Exception\InvalidArgumentException
     */
    protected function buildWhereFromFilter(ColumnInterface $column, $identifier, array $filterDefinition)
    {
        $predicate = new Predicate();

        $value    = $filterDefinition['value'];
        $operator = $filterDefinition['operator'];

        // Pravedeme neuplny string na DbDate
        if ('date' == $column->getAttributes()->getFormat()) {
            $value = $this->convertLocaleDateToDbDate($value);
        }

        switch ($operator) {
            case AbstractPlatform::OPERATOR_EQUAL:
                $where = $predicate->equalTo($identifier, $value);
                break;
            case AbstractPlatform::OPERATOR_NOT_EQUAL:
                $where = $predicate->notEqualTo($identifier, $value);
                break;
            case AbstractPlatform::OPERATOR_LESS:
                $where = $predicate->lessThan($identifier, $value);
                break;
            case AbstractPlatform::OPERATOR_LESS_OR_EQUAL:
                $where = $predicate->lessThanOrEqualTo($identifier, $value);
                break;
            case AbstractPlatform::OPERATOR_GREATER:
                $where = $predicate->greaterThan($identifier, $value);
                break;
            case AbstractPlatform::OPERATOR_GREATER_OR_EQUAL:
                $where = $predicate->greaterThanOrEqualTo($identifier, $value);
                break;
            case AbstractPlatform::OPERATOR_BEGINS_WITH:
                $where = $predicate->like($identifier, $value . "%");
                break;
            case AbstractPlatform::OPERATOR_NOT_BEGINS_WITH:
                $where = $predicate->notLike($identifier, $value . "%");
                break;
            case AbstractPlatform::OPERATOR_IN:
                $where = $predicate->in($identifier, $value);
                break;
            case AbstractPlatform::OPERATOR_NOT_IN:
                $where = $predicate->notIn($identifier, $value);
                break;
            case AbstractPlatform::OPERATOR_ENDS_WITH:
                $where = $predicate->like($identifier, "%" . $value);
                break;
            case AbstractPlatform::OPERATOR_NOT_ENDS_WITH:
                $where = $predicate->notLike($identifier, "%" . $value);
                break;
            case AbstractPlatform::OPERATOR_CONTAINS:
                $where = $predicate->like($identifier, "%" . $value . "%");
                break;
            case AbstractPlatform::OPERATOR_NOT_CONTAINS:
                $where = $predicate->notLike($identifier, "%" . $value . "%");
                break;
            default:
                throw new Exception\InvalidArgumentException('Invalid filter operator');
        }

        return $where;
    }

    /**
     * @param  AdapterInterface $adapter
     * @return $this
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Set Select
     *
     * @param  Select $select
     * @return $this
     */
    public function setSelect(Select $select)
    {
        $this->select = $select;

        return $this;
    }

    /**
     * Return Select
     *
     * @return Select
     */
    public function getSelect()
    {
        return $this->select;
    }
}