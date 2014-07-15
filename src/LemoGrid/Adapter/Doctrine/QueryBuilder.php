<?php

namespace LemoGrid\Adapter\Doctrine;

use DateTime;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder AS DoctrineQueryBuilder;
use LemoGrid\Adapter\AbstractAdapter;
use LemoGrid\Column\AbstractColumn;
use LemoGrid\Column\ColumnInterface;
use LemoGrid\Column\Concat as ColumnConcat;
use LemoGrid\Column\ConcatGroup as ColumnConcatGroup;
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

        $summaryData = array();
        for ($indexRow = 0; $indexRow < $rowsCount; $indexRow++) {
            $item = $rows[$indexRow];

            if (isset($item[0])) {
                $item = $this->mergeSubqueryItem($item);
            }

            $data = array();
            for ($indexCol = 0; $indexCol < $columnsCount; $indexCol++) {
                $column = $columns[$indexCol];

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
                    $hasValue = false;

                    foreach($column->getOptions()->getIdentifiers() as $index => $identifier) {
                        $val = $this->findValue($identifier, $item);

                        if(!empty($val)) {
                            if($val instanceof DateTime) {
                                $val = $value->format('Y-m-d H:i:s');
                            }

                            $values[$index] = $val;

                            if ('' !== $val) {
                                $hasValue = true;
                            }
                        } else {
                            $values[$index] = '';
                        }
                    }

                    $patternCount = count($values);
                    $patternCountParts = substr_count($column->getOptions()->getPattern(), '%s');
                    if (true === $hasValue && $patternCount > 0 && $patternCount == $patternCountParts) {
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
                        if (!empty($line)) {
                            $values[] = vsprintf($column->getOptions()->getPattern(), $line);
                        } else {
                            $values[] = null;
                        }
                    }

                    $value = implode($column->getOptions()->getSeparator(), $values);

                    unset($values, $valuesLine, $identifier);
                }

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

            $this->getResultSet()->append($data);
            unset($data);
        }

        // Calculate user data (SummaryRow)
        if (isset($dataSum)) {
            for ($indexCol = 0; $indexCol < $columnsCount; $indexCol++) {
                $column = $columns[$indexCol];

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
            for ($indexCol = 0; $indexCol < $columnsCount; $indexCol++) {
                $col = $columns[$indexCol];

                if($col->getAttributes()->getIsSearchable()) {

                    // Jsou definovane filtry pro sloupec
                    if(!empty($filter['rules'][$col->getName()])) {

                        $whereColSub = array();
                        foreach ($filter['rules'][$col->getName()] as $filterDefinition) {

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
                if($grid->has($sortColumn)) {
                    if($grid->get($sortColumn) instanceof ColumnConcat || $grid->get($sortColumn) instanceof ColumnConcatGroup) {
                        foreach($grid->get($sortColumn)->getOptions()->getIdentifiers() as $identifier){
                            if(count($this->getQueryBuilder()->getDQLPart('orderBy')) == 0) {
                                $method = 'orderBy';
                            } else {
                                $method = 'addOrderBy';
                                $sortDirect = 'asc';
                            }

                            $this->getQueryBuilder()->{$method}($identifier, $sortDirect);
                        }
                    } else {
                        if(count($this->getQueryBuilder()->getDQLPart('orderBy')) == 0) {
                            $method = 'orderBy';
                        } else {
                            $method = 'addOrderBy';
                        }

                        $this->getQueryBuilder()->{$method}($grid->get($sortColumn)->getIdentifier(), $sortDirect);
                    }
                }
            }
        }

        $resultCount = $this->getQueryBuilder()->getQuery()->getArrayResult();

        // Calculate offset
        $offset = $numberVisibleRows * $numberCurrentPage - $numberVisibleRows;
        if($offset < 0) {
            $offset = 0;
        }

        $this->getQueryBuilder()->setMaxResults($numberVisibleRows);
        $this->getQueryBuilder()->setFirstResult($offset);

        $result = $this->getQueryBuilder()->getQuery()->getArrayResult();

        $this->countItems = count($result);
        $this->countItemsTotal = count($resultCount);

        return $result;
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
    protected function findValue($identifier, array $item, $depth = 0)
    {
        if (0 == $depth) {
            $identifier = $this->buildIdententifier($identifier);
        }

        $identifierNext = substr($identifier, strpos($identifier, '.') + 1);

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
                $where = $expr->neq($identifier, "'" . $value . "'");
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
                $where = $expr->notLike($identifier, "'" . $value . "%'");
                break;
            case AbstractPlatform::OPERATOR_IN:
                $where = $expr->in($identifier, "'" . $value . "'");
                break;
            case AbstractPlatform::OPERATOR_NOT_IN:
                $where = $expr->notIn($identifier, "'" . $value . "'");
                break;
            case AbstractPlatform::OPERATOR_ENDS_WITH:
                $where = $expr->like($identifier, "'%" . $value . "'");
                break;
            case AbstractPlatform::OPERATOR_NOT_ENDS_WITH:
                $where = $expr->notLike($identifier, "'%" . $value . "'");
                break;
            case AbstractPlatform::OPERATOR_CONTAINS:
                $where = $expr->like($identifier, "'%" . $value . "%'");
                break;
            case AbstractPlatform::OPERATOR_NOT_CONTAINS:
                $where = $expr->notLike($identifier, "'%" . $value . "%'");
                break;
            default:
                throw new Exception\InvalidArgumentException('Invalid filter operator');
        }

        return $where;
    }

    protected function mergeSubqueryItem($item)
    {
        // Nacteme si samostatne data entity a seznam poli
        $fields = $item;
        $item = $item[0];
        unset($fields[0]);

        // Projdeme vsechna pole, ktera mame odebrat
        foreach ($fields as $name => $value) {
            if (is_null($value)) {

                // Jedna se o Pole
                if (is_array($item)) {
                    if (isset($item[$name])) {
                        $item[$name] = $value;
                    }
                }
            }
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
