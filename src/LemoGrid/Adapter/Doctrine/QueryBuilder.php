<?php

namespace LemoGrid\Adapter\Doctrine;

use DateTime;
use Doctrine\ORM\QueryBuilder AS DoctrineQueryBuilder;
use LemoGrid\Adapter\AbstractAdapter;
use LemoGrid\Column\Concat as ColumnConcat;
use LemoGrid\Column\ConcatGroup as ColumnConcatGroup;
use LemoGrid\Exception;
use LemoGrid\GridInterface;

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
     * @var array
     */
    protected $relations = array();

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
        $this->findRelations($this->aliasRoot);

        foreach ($this->executeQuery() as $indexRow => $item)
        {
            $data = array();
            foreach($this->getGrid()->getColumns() as $column) {
                $colIdentifier = $column->getIdentifier();
                $colname = $column->getName();
                $data[$colname] = null;

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

                    if (!empty($values)) {
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
                if(preg_match_all('/%(_?[a-zA-Z0-9\._-]+)%/', $value, $matches)) {
                    foreach($matches[0] as $key => $match) {
                        if ('%_index%' == $matches[0][$key]) {
                            $value = str_replace($matches[0][$key], $indexRow, $value);
                        } else {
                            $value = str_replace($matches[0][$key], $this->findValue($matches[1][$key], $item), $value);
                        }
                    }
                }

                $data[$colname] = $value;
                $column->setValue($value);
            }

            $this->getData()->append($data);
        }

        return $this;
    }

    /**
     * @return array
     */
    protected function executeQuery()
    {
        $grid = $this->getGrid();
        $filters = $grid->getParam('filters');
        $resultCount = $this->getQueryBuilder()->getQuery()->getScalarResult();

        // WHERE
        foreach($grid->getColumns() as $col)
        {
            if($col->getAttributes()->getIsSearchable()) {
                $prepend = null;
                $append = null;

                if(array_key_exists($col->getName(), $filters)) {
                    if($col instanceof ColumnConcat) {
                        $or = $this->getQueryBuilder()->expr()->orx();
                        foreach($col->getOptions()->getIdentifiers() as $identifier){
                            $or->add($identifier . " LIKE '%" . $filters[$col->getName()]['value'] . "%'");
                        }
                        $this->getQueryBuilder()->andWhere($or);
                    } else {
                        if('text' == $col->getAttributes()->getSearchElement()) {
                            $prepend = $append = '%';
                        }

                        $this->getQueryBuilder()->andWhere($col->getIdentifier() . " LIKE '" . $prepend . $filters[$col->getName()]['value'] . $append . "'");
                    }
                }
            }
        }

        if($grid->has($grid->getPlatform()->getSortColumn())) {
            if($grid->get($grid->getPlatform()->getSortColumn()) instanceof ColumnConcat) {
                foreach($grid->get($grid->getPlatform()->getSortColumn())->getOptions()->getIdentifiers() as $identifier){
                    if(count($this->getQueryBuilder()->getDQLPart('orderBy')) == 0) {
                        $method = 'orderBy';
                    } else {
                        $method = 'addOrderBy';
                    }

                    $this->getQueryBuilder()->{$method}($identifier, $grid->getPlatform()->getSortDirect());
                }
            } else {
                $this->getQueryBuilder()->orderBy($grid->get($grid->getPlatform()->getSortColumn())->getIdentifier(), $grid->getPlatform()->getSortDirect());
            }
        }

        $offset = $this->getNumberOfVisibleRows() * $this->getNumberOfCurrentPage() - $this->getNumberOfVisibleRows();

        if($offset < 0) {
            $offset = 0;
        }

        $this->getQueryBuilder()->setMaxResults($this->getNumberOfVisibleRows());
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

        $this->aliasRoot = $root;
        $this->aliases = array();

        if(!empty($join[$root])) {
            foreach($join[$root] as $j) {
                preg_match('/JOIN (([a-zA-Z0-9_-]+)\.([a-zA-Z0-9\._-]+))( as| AS)?( )?([a-zA-Z0-9_]+)?/', $j->__toString(), $match);

                $this->aliases[$match[2]][] = array(
                    'alias' => $match[6],
                    'relation' => $match[3]
                );
            }
        }

        return $this;
    }

    /**
     * Find relations from given alias name
     *
     * @param  string $alias
     * @param  bool   $sub
     * @return QueryBuilder
     */
    protected function findRelations($alias, $sub = false)
    {
        if(count($this->aliases) == 0) {
            return array();
        }

        foreach($this->aliases[$alias] as $item) {
            if($sub == false) {
                $this->relations[$item['alias']] = $item['relation'];
            } else {
                $this->relations[$item['alias']] = $this->relations[$alias] . '/' . $item['relation'];
            }

            if(isset($this->aliases[$item['alias']])) {
                $this->findRelations($item['alias'], true);
            }
        }

        return $this;
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
     * Set QueryBuilder
     *
     * @param  DoctrineQueryBuilder $queryBuilder
     * @return QueryBuilder
     */
    public function setQueryBuilder(DoctrineQueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;

        return $this;
    }

    /**
     * Return QueryBuilder
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }
}
