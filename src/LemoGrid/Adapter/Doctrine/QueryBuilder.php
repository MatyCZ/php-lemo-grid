<?php

namespace LemoGrid\Adapter\Doctrine;

use DateTime;
use Doctrine\ORM\QueryBuilder AS DoctrineQueryBuilder;
use LemoGrid\Adapter\AbstractAdapter;
use LemoGrid\Column\Concat;
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
     * @return Collection
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

        foreach ($this->executeQuery() as $item)
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

                if($value instanceof DateTime) {
                    $value = $value->format('Y-m-d H:i:s');
                }

                if($column instanceof \LemoGrid\Column\Concat) {
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

                    $value = implode($column->getSeparator(), $values);

                    unset($values, $identifier);
                }

                // Projdeme data a nahradime data ve formatu %xxx%
                if(preg_match_all('/%([a-zA-Z0-9\._-]+)%/', $value, $matches)) {
                    foreach($matches[0] as $key => $match) {
                        $value = str_replace($matches[0][$key], $this->findValue($matches[1][$key], $item), $value);
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

        $resultCount = $this->getQueryBuilder()->getQuery()->getScalarResult();

        $this->getQueryBuilder()->setMaxResults($grid->getOptions()->getRecordsPerPage());
        $this->getQueryBuilder()->setFirstResult($grid->getOptions()->getRecordsPerPage() * $this->getNumberOfCurrentPage() - $grid->getOptions()->getRecordsPerPage());

        // WHERE
        foreach($grid->getColumns() as $col)
        {
            if($col->getAttributes()->getIsSearchable()) {
                $prepend = null;
                $append = null;
//
//                \Zend\Debug\Debug::dump($grid->getParams());
//                exit;

                if($grid->hasParam($col->getName())) {
                    if($col instanceof Concat) {
                        $or = $this->getQueryBuilder()->expr()->orx();
                        foreach($col->getIdentifiers() as $identifier){
                            $or->add($identifier . " LIKE '%" . $grid->getParam($col->getName()) . "%'");
                        }
                        $this->getQueryBuilder()->andWhere($or);
                    } else {
                        if('text' == $col->getAttributes()->getSearchElement()) {
                            $prepend = $append = '%';
                        }

                        $this->getQueryBuilder()->andWhere($col->getIdentifier() . " LIKE '" . $prepend . $grid->getParam($col->getName()) . $append . "'");
                    }
                }
            }
        }

        // ORDER
//        if('concat' == $grid->get($this->getSortColumn())->getType()) {
//            foreach($grid->get($this->getSortColumn())->getIdentifiers() as $identifier){
//                if(count($this->getQueryBuilder()->getDQLPart('orderBy')) == 0) {
//                    $method = 'orderBy';
//                } else {
//                    $method = 'addOrderBy';
//                }
//
//                $this->getQueryBuilder()->{$method}($identifier, $this->getSortDirect());
//            }
//        } else {
        if($grid->has($grid->getSortColumn())) {
            $this->getQueryBuilder()->orderBy($grid->get($grid->getSortColumn())->getIdentifier(), $grid->getSortDirect());
        }
//        }

        $offset = $grid->getOptions()->getRecordsPerPage() * $this->getNumberOfCurrentPage() - $grid->getOptions()->getRecordsPerPage();

        if($offset < 0) {
            $offset = 0;
        }

        $this->getQueryBuilder()->setMaxResults($grid->getOptions()->getRecordsPerPage());
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
        if(array_key_exists($name, $item) && (null === $relationAlias || !array_key_exists($relationAlias, $this->relations))) {
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
