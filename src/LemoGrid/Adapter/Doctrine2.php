<?php

namespace LemoGrid\Adapter;

use Doctrine\ORM\QueryBuilder;
use LemoGrid\Exception;

class Doctrine2 extends AbstractAdapter implements AdapterInterface
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
     * @var QueryBuilder
     */
    protected $queryBuilder = null;

    /**
     * @var array
     */
    protected $relations = array();

    /**
     * @throws Exception\UnexpectedValueException
     * @return array
     */
    public function getData()
    {
        if(null === $this->getQueryBuilder()) {
            throw new Exception\UnexpectedValueException("No QueryBuilder instance given");
        }

        $grid = $this->getGrid();
        $data = array();

        $this->findAliases();
        $this->findRelations($this->aliasRoot);

        foreach ($this->executeQuery() as $item)
        {
            $rowData = array();

            foreach($grid->getColumns() as $column) {
                $colName = $column->getIdentifier();
                $rowData[$colName] = null;

                // Nacteme si data radku
                $value = $column->renderValue($this->findValue($colName, $item, $this->relations));

                if($value instanceof \DateTime) {
                    $value = $value->format('Y-m-d H:i:s');
                }

                if('concat' == $column->getType()) {
                    $values = array();
                    foreach($column->getIdentifiers() as $identifier) {
                        $val = $this->findValue($identifier, $item, $this->relations);

                        if(!empty($val)) {
                            if($val instanceof \DateTime) {
                                $val = $value->format('Y-m-d H:i:s');
                            }

                            $values[] = $val;
                        }
                    }

                    $value = implode($column->getSeparator(), $values);

                    unset($values, $identifier);
                }

                // Projdeme data a nahradime data ve formatu %xxx%
                if(preg_match_all('/%([a-zA-Z0-9\._-]+)%/', $value, $matches)) {
                    foreach($matches[0] as $key => $match) {
                        $value = str_replace($matches[0][$key], $this->findValue($matches[1][$key], $item, $this->relations), $value);
                    }
                }

                $rowData[$colName] = $value;
            }

            $data[] = $rowData;
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function executeQuery()
    {
        $grid = $this->getGrid();

        $resultCount = $this->getQueryBuilder()->getQuery()->getScalarResult();

        $this->getQueryBuilder()->setMaxResults($grid->getRecordsPerPage());
        $this->getQueryBuilder()->setFirstResult($grid->getRecordsPerPage() * $this->getNumberOfCurrentPage() - $grid->getRecordsPerPage());

        // WHERE
        foreach($grid->getColumns() as $col)
        {

            if(true === $col->getIsSearchable()) {
                $prepend = null;
                $append = null;

                if($grid->getQueryParam($col->getName())) {

                    if('concat' == $col->getType()) {
                        $or = $this->getQueryBuilder()->expr()->orx();
                        foreach($col->getIdentifiers() as $identifier){
                            $or->add($identifier . " LIKE '%" . $grid->getQueryParam($col->getName()) . "%'");
                        }
                        $this->getQueryBuilder()->andWhere($or);
                    } else {
                        if('text' == $col->getSearchElement()) {
                            $prepend = $append = '%';
                        }

                        $this->getQueryBuilder()->andWhere($col->getIdentifier() . " LIKE '" . $prepend . $grid->getQueryParam($col->getName()) . $append . "'");
                    }
                }
            }
        }

        // ORDER
        if('concat' == $grid->getColumn($this->getSortColumn())->getType()) {
            foreach($grid->getColumn($this->getSortColumn())->getIdentifiers() as $identifier){
                if(count($this->getQueryBuilder()->getDQLPart('orderBy')) == 0) {
                    $method = 'orderBy';
                } else {
                    $method = 'addOrderBy';
                }

                $this->getQueryBuilder()->{$method}($identifier, $this->getSortDirect());
            }
        } else {
            $this->getQueryBuilder()->orderBy($grid->getColumn($this->getSortColumn())->getIdentifier(), $this->getSortDirect());
        }

        $offset = $grid->getRecordsPerPage() * $this->getNumberOfCurrentPage() - $grid->getRecordsPerPage();

        if($offset < 0) {
            $offset = 0;
        }

        $this->getQueryBuilder()->setMaxResults($grid->getRecordsPerPage());
        $this->getQueryBuilder()->setFirstResult($offset);

        $result = $this->getQueryBuilder()->getQuery()->getArrayResult();

        $this->countItems = count($result);
        $this->countItemsTotal = count($resultCount);

        return $result;
    }

    /**
     * Find aliases used in Query
     *
     * @return Doctrine2
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
     * Find relations by given alias name
     *
     * @param  string $alias
     * @param  bool   $sub
     * @return Doctrine2
     */
    protected function findRelations($alias, $sub = false)
    {
        if(count($this->aliases) == 0) {
            return array();
        }

        foreach($this->aliases[$alias] as $item)
        {
            if($sub == false) {
                $this->relations[$item['alias']] = $item['relation'];
            } else {
                $this->relations[$item['alias']] = $this->relations[$alias] . '/' . $item['relation'];
            }

            if(isset($this->aliases[$item['alias']])) {
                $rel = $this->findRelations($item['alias'], true);

                foreach($rel as $key => $value) {
                    $this->relations[$key] = $value;
                }
            }
        }

        return $this;
    }

    /**
     * Find value for column
     *
     * @param  string $columnName
     * @param  array $item
     * @return null|string
     */
    protected function findValue($columnName, array $item)
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
        if(isset($item[$name]) && ($relationAlias == null || !isset($relations[$relationAlias]))) {
            return $item[$name];
        }

        // Try find item in relations
        if(array_key_exists($relationAlias, $this->relations)) {
            $relation = explode('/', $this->relations[$relationAlias]);
            $itemRelation = $item;

            // Read data from relation
            foreach ($relation as $rel) {
                if(count($itemRelation[$rel]) == 1 && isset($itemRelation[$rel][0])) {
                    $itemRelation = $itemRelation[$rel][0];
                } else {
                    $itemRelation = $itemRelation[$rel];
                }
            }

            if(isset($itemRelation[$name])) {
                return $itemRelation[$name];
            } else {
                return null;
            }
        }

        return null;
    }

    /**
     * Set QueryBuilder
     *
     * @param QueryBuilder $queryBuilder
     * @return Doctrine2
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder)
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
