<?php

namespace LemoGrid\Adapter\Doctrine;

use DateTime;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder AS DoctrineQueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use LemoGrid\Adapter\AbstractAdapter;
use LemoGrid\Column\AbstractColumn;
use LemoGrid\Column\Buttons;
use LemoGrid\Column\ColumnInterface;
use LemoGrid\Column\Concat as ColumnConcat;
use LemoGrid\Column\ConcatGroup as ColumnConcatGroup;
use LemoGrid\Event\AdapterEvent;
use LemoGrid\Exception;
use LemoGrid\GridInterface;
use LemoGrid\Platform\AbstractPlatform;

class QueryBuilder extends AbstractAdapter
{
    /**
     * @var string
     */
    protected $aliasRoot;

    /**
     * @var array
     */
    protected $aliases = array();

    /**
     * @var DoctrineQueryBuilder
     */
    protected $queryBuilder = null;

    /**
     * Return adapter name
     *
     * @return string
     */
    public function getName()
    {
        return 'doctrine_queryBuilder';
    }

    /**
     * @throws Exception\UnexpectedValueException
     * @return QueryBuilder
     */
    public function populateData()
    {
        if(!$this->getGrid() instanceof GridInterface) {
            throw new Exception\UnexpectedValueException("No Grid instance given");
        }
        if(!$this->getQueryBuilder() instanceof DoctrineQueryBuilder) {
            throw new Exception\UnexpectedValueException("No QueryBuilder instance given");
        }

        $this->findAliases();

        $rows = $this->executeQuery();
        $rowsCount = count($rows);
        $columns = $this->getGrid()->getIterator()->toArray();
        $columnsCount = $this->getGrid()->getIterator()->count();
        $this->countItems = $rowsCount;

        $data = array();
        $summaryData = array();
        for ($indexRow = 0; $indexRow < $rowsCount; $indexRow++) {
            $item = $rows[$indexRow];

            if (isset($item[0])) {
                $item = $this->mergeSubqueryItem($item);
            }

            $data = array();
            foreach($columns as $indexCol => $column) {

                // Sloupec je skryty, takze ho preskocime
                if (true === $column->getAttributes()->getIsHidden()) {
                    continue;
                }

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
                    if(null !== $value && preg_match_all('/%(_?[a-zA-Z0-9\._-]+)%/', $value, $matches)) {
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
            }

            $this->getResultSet()->append($data);
        }

        // Calculate user data (SummaryRow)
        foreach($columns as $indexCol => $column) {

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
        $event->setData($data);
        $event->setGridName($this->getGrid()->getName());

        $this->getGrid()->getEventManager()->trigger(AdapterEvent::EVENT_LOAD_DATA, $this, $event);

        return $this;
    }

    /**
     * @throws \Exception
     * @return array
     */
    protected function executeQuery()
    {
        $grid = $this->getGrid();
        $filter = $grid->getParam('filters');
        $numberCurrentPage = $grid->getPlatform()->getNumberOfCurrentPage();
        $numberVisibleRows = $grid->getPlatform()->getNumberOfVisibleRows();
        $sort = $grid->getPlatform()->getSort();

        $columns = $this->getGrid()->getIterator()->toArray();
        $columnsCount = $this->getGrid()->getIterator()->count();

        // WHERE
        if (!empty($filter['rules'])) {

            $whereCol = array();
            foreach($columns as $indexCol => $col) {
                if($col->getAttributes()->getIsSearchable() && true !== $col->getAttributes()->getIsHidden()) {

                    // Jsou definovane filtry pro sloupec
                    if(!empty($filter['rules'][$col->getName()])) {

                        $whereColSub = array();
                        foreach ($filter['rules'][$col->getName()] as $filterDefinition) {
                            if ('~' == $filterDefinition['operator']) {

                                // Sestavime WHERE
                                $filterWords = explode(' ', $filterDefinition['value']);

                                $wheres = array();
                                if($col instanceof ColumnConcat || $col instanceof ColumnConcatGroup) {

                                    // Operator AND
                                    if ('and' === $col->getAttributes()->getSearchGroupOperator()) {
                                        $filterWordsCombination = $this->createWordsCombination($filterWords);

                                        if (count($col->getOptions()->getIdentifiers()) > 1) {
                                            $concat = $this->buildConcat($col->getOptions()->getIdentifiers());
                                            foreach ($filterWordsCombination as $wordsCombination) {
                                                $wheres[] = $this->buildWhereFromFilter($col, $concat, array(
                                                    'operator' => '~',
                                                    'value'    => implode('%', $wordsCombination),
                                                ));
                                            }

                                            // Pridame WHERE do QueryBuilderu
                                            $exp = new Expr\Orx();
                                            $exp->addMultiple($wheres);

                                            $whereColSub[] = $exp;
                                        } else {
                                            foreach ($col->getOptions()->getIdentifiers() as $identifier) {
                                                foreach ($filterWordsCombination as $wordsCombination) {
                                                    $wheres[] = $this->buildWhereFromFilter($col, $identifier, array(
                                                        'operator' => '~',
                                                        'value'    => implode('%', $wordsCombination),
                                                    ));
                                                }
                                            }

                                            // Pridame WHERE do QueryBuilderu
                                            $exp = new Expr\Orx();
                                            $exp->addMultiple($wheres);

                                            $whereColSub[] = $exp;
                                        }
                                    }

                                    // Operator OR
                                    if ('or' === $col->getAttributes()->getSearchGroupOperator()) {
                                        if (count($filterWords) > 1) {
                                            foreach ($col->getOptions()->getIdentifiers() as $identifier) {
                                                $whereColTerm = array();
                                                foreach ($filterWords as $filterWord) {
                                                    $whereColTerm[] = $this->buildWhereFromFilter($col, $identifier, array(
                                                        'operator' => '~',
                                                        'value'    => $filterWord,
                                                    ));
                                                }

                                                // Sloucime podminky sloupce pomoci OR (z duvodu Concat sloupce)
                                                $exprColTerm = new Expr\Orx();
                                                $exprColTerm->addMultiple($whereColTerm);

                                                $wheres[] = $exprColTerm;
                                            }

                                            // Pridame WHERE do QueryBuilderu
                                            $exp = new Expr\Andx();
                                            $exp->addMultiple($wheres);

                                            $whereColSub[] = $exp;
                                        } elseif (isset($filterWords[0])) {
                                            foreach ($col->getOptions()->getIdentifiers() as $identifier) {
                                                $wheres[] = $this->buildWhereFromFilter($col, $identifier, array(
                                                    'operator' => '~',
                                                    'value'    => $filterWords[0],
                                                ));
                                            }

                                            // Pridame WHERE do QueryBuilderu
                                            $exp = new Expr\Orx();
                                            $exp->addMultiple($wheres);

                                            $whereColSub[] = $exp;
                                        }
                                    }
                                } else {
                                    foreach ($filterWords as $filterWord) {
                                        $wheres[] = $this->buildWhereFromFilter($col, $col->getIdentifier(), array(
                                            'operator' => '~',
                                            'value'    => $filterWord,
                                        ));
                                    }

                                    // Operator AND
                                    if ('and' === $col->getAttributes()->getSearchGroupOperator()) {
                                        $exp = new Expr\Andx();
                                        $exp->addMultiple($wheres);
                                    }

                                    // Operator OR
                                    if ('or' === $col->getAttributes()->getSearchGroupOperator()) {
                                        $exp = new Expr\Orx();
                                        $exp->addMultiple($wheres);
                                    }

                                    $whereColSub[] = $exp;
                                }
                            } else {
                                // Sestavime filtr pro jednu podminku sloupce
                                $exprFilterColSub = array();
                                if($col instanceof ColumnConcat || $col instanceof ColumnConcatGroup) {
                                    foreach ($col->getOptions()->getIdentifiers() as $identifier) {
                                        $exprFilterColSub[] = $this->buildWhereFromFilter($col, $identifier, $filterDefinition);
                                    }
                                } else {
                                    $exprFilterColSub[] = $this->buildWhereFromFilter($col, $col->getIdentifier(), $filterDefinition);
                                }

                                // Sloucime podminky sloupce pomoci OR (z duvodu Concat sloupce)
                                $exprColSub = new Expr\Orx();
                                $exprColSub->addMultiple($exprFilterColSub);

                                $whereColSub[] = $exprColSub;
                            }
                        }

                        //
                        if ('and' == $filter['operator']) {
                            $exprCol = new Expr\Andx();
                            $exprCol->addMultiple($whereColSub);
                        } else {
                            $exprCol = new Expr\Orx();
                            $exprCol->addMultiple($whereColSub);
                        }

                        $whereCol[] = $exprCol;
                    }
                }
            }

            // Slouceni EXPR jednotlivych sloupcu do jednoho WHERE
            if ('and' == $filter['operator']) {
                $exprCols = new Expr\Andx();
                $exprCols->addMultiple($whereCol);
            } else {
                $exprCols = new Expr\Orx();
                $exprCols->addMultiple($whereCol);
            }

            // Pridame k vychozimu WHERE i WHERE z filtrace sloupcu
            $this->getQueryBuilder()->andWhere($exprCols);
        }

        // ORDER
        if (!empty($sort)) {
            foreach ($sort as $sortColumn => $sortDirect) {
                if ($grid->has($sortColumn)) {
                    if (false !== $grid->get($sortColumn)->getAttributes()->getIsSortable() && true !== $grid->get($sortColumn)->getAttributes()->getIsHidden()) {
                        if ($grid->get($sortColumn) instanceof ColumnConcat || $grid->get($sortColumn) instanceof ColumnConcatGroup) {
                            foreach($grid->get($sortColumn)->getOptions()->getIdentifiers() as $identifier){
                                if (count($this->getQueryBuilder()->getDQLPart('orderBy')) == 0) {
                                    $method = 'orderBy';
                                } else {
                                    $method = 'addOrderBy';
                                    $sortDirect = 'asc';
                                }

                                $this->getQueryBuilder()->{$method}($identifier, $sortDirect);
                            }
                        } else {
                            if (count($this->getQueryBuilder()->getDQLPart('orderBy')) == 0) {
                                $method = 'orderBy';
                            } else {
                                $method = 'addOrderBy';
                            }

                            $this->getQueryBuilder()->{$method}($grid->get($sortColumn)->getIdentifier(), $sortDirect);
                        }
                    }
                }
            }
        }

        // Calculate offset
        if ($numberVisibleRows > 0) {
            $offset = $numberVisibleRows * $numberCurrentPage - $numberVisibleRows;
            if($offset < 0) {
                $offset = 0;
            }

            $this->getQueryBuilder()->setFirstResult($offset);
            $this->getQueryBuilder()->setMaxResults($numberVisibleRows);
        }

        $query = $this->getQueryBuilder()->getQuery();
        $query->setHydrationMode(Query::HYDRATE_ARRAY);

        $paginator = new Paginator($query, true);

        $this->countItemsTotal = $paginator->count();

        return $paginator->getIterator();
    }

    /**
     * Find aliases used in Query
     *
     * @return QueryBuilder
     */
    protected function findAliases()
    {
        $from = $this->getQueryBuilder()->getDqlPart('from');
        $join = $this->getQueryBuilder()->getDqlPart('join');
        $root = $from[0]->getAlias();

        $this->aliases = array();

        if(!empty($join[$root])) {
            foreach($join[$root] as $j) {
                preg_match('/JOIN (([a-zA-Z0-9_-]+)\.([a-zA-Z0-9\._-]+))( as| AS)?( )?([a-zA-Z0-9_]+)?/', $j->__toString(), $match);

                $this->aliases[$match[6]] = $match[2] . '.' . $match[3];
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
        if (0 == $depth) {
            $identifier = $this->buildIdententifier($identifier);
        }

        $identifierNext = $identifier;
        if (false !== strpos($identifier, '.')) {
            $identifierNext = substr($identifier, strpos($identifier, '.') + 1);
        }

        $parts = explode('.', $identifierNext);

        if (isset($item[$parts[0]]) && count($parts) > 1) {
            return $this->findValue($identifierNext, $item[$parts[0]], $depth+1);
        }

        if (isset($item[$identifierNext])) {
            return $item[$identifierNext];
        } else {
            if (isset($item[0])) {

                $return = array();
                foreach ($item as $it) {
                    if (isset($it[$identifierNext])) {
                        $return[] = $it[$identifierNext];
                    }
                }

                return $return;
            }
        }

        return null;
    }

    /**
     * Sestavi identifier
     *
     * @param  string $identifier
     * @return string
     */
    protected function buildIdententifier($identifier)
    {
        $identifier = str_replace('_', '.', $identifier);

        // Determinate column name and alias name
        $identifierFirst = substr($identifier, 0, strpos($identifier, '.'));

        if (isset($this->aliases[$identifierFirst])) {
            $identifier = str_replace($identifierFirst . '.', $this->aliases[$identifierFirst] . '.', $identifier);

            return $this->buildIdententifier($identifier);
        }

        return $identifier;
    }

    /**
     * Sestavi CONCAT z predanych casti
     *
     * @param  array  $identifiers
     * @return Expr\Func
     */
    protected function buildConcat(array $identifiers)
    {
        $expr = new Expr();

        $firstPart = null;
        foreach ($identifiers as $index => $identifier) {
            $firstPart = "CASE WHEN  (" . $identifier . " IS NULL) THEN '' ELSE " . $identifier . " END";
            unset($identifiers[$index]);
            break;
        }

        if (count($identifiers) > 1) {
            $secondPart = $this->buildConcat($identifiers);
        } elseif (count($identifiers) == 1) {
            $secondPart = current($identifiers);
            $secondPart = "CASE WHEN  (" . $secondPart . " IS NULL) THEN '' ELSE " . $secondPart . " END";
        } else {
            return $firstPart;
        }

        $concat = $expr->concat($firstPart, $secondPart);

        return $concat;
    }

    /**
     * @param  ColumnInterface $column
     * @param  string          $identifier
     * @param  array           $filterDefinition
     * @return Expr\Comparison
     * @throws Exception\InvalidArgumentException
     */
    protected function buildWhereFromFilter(ColumnInterface $column, $identifier, array $filterDefinition)
    {
        $expr = new Expr();

        $value    = $filterDefinition['value'];
        $operator = $filterDefinition['operator'];

        // Pravedeme neuplny string na DbDate
        if ('date' == $column->getAttributes()->getFormat()) {
            $value = $this->convertLocaleDateToDbDate($value);
        }

        switch ($operator) {
            case AbstractPlatform::OPERATOR_EQUAL:
                $where = $expr->eq($identifier, "'" . $value . "'");
                break;
            case AbstractPlatform::OPERATOR_NOT_EQUAL:
                $where = $expr->orX(
                    $expr->neq($identifier, "'" . $value . "'"),
                    $expr->isNull($column)
                );
                break;
            case AbstractPlatform::OPERATOR_LESS:
                $where = $expr->lt($identifier, "'" . $value . "'");
                break;
            case AbstractPlatform::OPERATOR_LESS_OR_EQUAL:
                $where = $expr->lte($identifier, "'" . $value . "'");
                break;
            case AbstractPlatform::OPERATOR_GREATER:
                $where = $expr->gt($identifier, "'" . $value . "'");
                break;
            case AbstractPlatform::OPERATOR_GREATER_OR_EQUAL:
                $where = $expr->gte($identifier, "'" . $value . "'");
                break;
            case AbstractPlatform::OPERATOR_BEGINS_WITH:
                $where = $expr->like($identifier, "'" . $value . "%'");
                break;
            case AbstractPlatform::OPERATOR_NOT_BEGINS_WITH:
                $where = $expr->orX(
                    $expr->notLike($identifier, "'" . $value . "%'"),
                    $expr->isNull($column)
                );
                break;
            case AbstractPlatform::OPERATOR_IN:
                $where = $expr->in($identifier, "'" . $value . "'");
                break;
            case AbstractPlatform::OPERATOR_NOT_IN:
                $where = $expr->orX(
                    $expr->notIn($identifier, "'" . $value . "'"),
                    $expr->isNull($column)
                );
                break;
            case AbstractPlatform::OPERATOR_ENDS_WITH:
                $where = $expr->like($identifier, "'%" . $value . "'");
                break;
            case AbstractPlatform::OPERATOR_NOT_ENDS_WITH:
                $where = $expr->orX(
                    $expr->notLike($identifier, "'%" . $value . "'"),
                    $expr->isNull($identifier)
                );
                break;
            case AbstractPlatform::OPERATOR_CONTAINS:
                $where = $expr->like($identifier, "'%" . $value . "%'");
                break;
            case AbstractPlatform::OPERATOR_NOT_CONTAINS:
                $where = $expr->orX(
                    $expr->notLike($identifier, "'%" . $value . "%'"),
                    $expr->isNull($identifier)
                );
                break;
            default:
                throw new Exception\InvalidArgumentException('Invalid filter operator');
        }

        return $where;
    }

    /**
     * @param  array $items
     * @param  array $perms
     * @param  array $permsBuilded
     * @return array
     */
    protected function createWordsCombination($items, $perms = array(), $permsBuilded = array())
    {
        if (empty($items)) {
            $permsBuilded[] = $perms;
        } else {
            for ($i = count($items) - 1; $i >= 0; --$i) {
                $newitems = $items;
                $newperms = $perms;
                list($foo) = array_splice($newitems, $i, 1);
                array_unshift($newperms, $foo);

                $permsBuilded = $this->createWordsCombination($newitems, $newperms, $permsBuilded);
            }
        }

        return $permsBuilded;
    }

    /**
     * @param  array $item
     * @return array
     */
    protected function mergeSubqueryItem(array $item)
    {
        // Nacteme si samostatne data entity a seznam poli
        $fields = $item;
        $item = $item[0];
        unset($fields[0]);

        // Projdeme vsechna pole, ktera mame odebrat
        foreach ($fields as $name => $value) {
//            if (is_null($value)) {
//
//                // Jedna se o Pole
//                if (is_array($item)) {
//                    if (isset($item[$name])) {
                        $item[$name] = $value;
//                    }
//                }
//            }
        }

        return $item;
    }

    /**
     * Set QueryBuilder
     *
     * @param  DoctrineQueryBuilder $queryBuilder
     * @return DoctrineQueryBuilder
     */
    public function setQueryBuilder(DoctrineQueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;

        return $this;
    }

    /**
     * Return QueryBuilder
     *
     * @return DoctrineQueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }
}
