<?php

/**
 * @namespace
 */
namespace LemoBase\Grid\Adapter;

use LemoBase\Grid\Adapter\AbstractAdapter;
use LemoBase\Grid\Adapter\AdapterInterface;

/**
 * LemoBase_Grid_Adapter_Doctrine2
 *
 * @category   LemoBase
 * @package    LemoBase_Grid
 * @subpackage Adapter
 */
class Doctrine2 extends AbstractAdapter implements AdapterInterface
{
    /**
     * @var \Doctrine\ORM\QueryBuilder
     */
    protected $_queryBuilder = null;

    /**
     * @return array
     */
    public function getData()
    {
        $grid = $this->getGrid();
        $qb = $this->getQueryBuilder();
        $data = array();

        if(null === $this->getQueryBuilder()) {
            throw new Exception\UnexpectedValueException("No QueryBuilder instance given");
        }

        // Najdeme aliasy relaci v JOINU
        $partFrom = $qb->getDqlPart('from');
        $partJoin = $qb->getDqlPart('join');

        $aliases = array();
        $rootAlias = $partFrom[0]->getAlias();

        if(isset($partJoin[$rootAlias]) && count($partJoin[$rootAlias]) > 0) {
            foreach($partJoin[$rootAlias] as $join) {
                preg_match('/JOIN (([a-zA-Z0-9_-]+)\.([a-zA-Z0-9\._-]+))( as| AS)?( )?([a-zA-Z0-9_]+)?/', $join->__toString(), $joinMatches);

                $aliases[$joinMatches[2]][] = array(
                    'alias' => $joinMatches[6],
                    'relation' => $joinMatches[3]
                );
            }
        }

        // Namapujeme si pouzite relace
        $relations = $this->_mapRelations($rootAlias, $aliases);

        foreach ($this->_executeQuery() as $item)
        {
            $rowData = array();

            foreach($grid->getColumns() as $column)
            {

                $colName = $column->getIdentifier();
                $rowData[$colName] = null;

                // Nacteme si data radku
                $value = $column->renderValue($this->_getRowData($colName, $item, $relations));

                if($value instanceof \DateTime) {
                    $value = $value->format('Y-m-d H:i:s');
                }

                if('concat' == $column->getType()) {
                    $values = array();
                    foreach($column->getIdentifiers() as $identifier) {
                        $val = $this->_getRowData($identifier, $item, $relations);

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
                        $value = str_replace($matches[0][$key], $this->_getRowData($matches[1][$key], $item, $relations), $value);
                    }
                }

                $rowData[$colName] = $value;
            }

            $data[] = $rowData;
        }

        return $data;
    }

    private function _executeQuery()
    {
        $grid = $this->getGrid();

        $qb = $this->_queryBuilder;

        $resultCount = $qb->getQuery()->getScalarResult();

        $qb->setMaxResults($grid->getRecordsPerPage());
        $qb->setFirstResult($grid->getRecordsPerPage() * $this->getNumberOfCurrentPage() - $grid->getRecordsPerPage());

        // WHERE
        foreach($grid->getColumns() as $col)
        {

            if(true === $col->getIsSearchable()) {
                $prepend = null;
                $append = null;

                if($grid->getQueryParam($col->getName())) {

                    if('concat' == $col->getType()) {
                        $or = $qb->expr()->orx();
                        foreach($col->getIdentifiers() as $identifier){
                            $or->add($identifier . " LIKE '%" . $grid->getQueryParam($col->getName()) . "%'");
                        }
                        $qb->andWhere($or);
                    } else {
                        if('text' == $col->getSearchElement()) {
                            $prepend = $append = '%';
                        }

                        $qb->andWhere($col->getIdentifier() . " LIKE '" . $prepend . $grid->getQueryParam($col->getName()) . $append . "'");
                    }
                }
            }
        }

        // ORDER
        if('concat' == $grid->getColumn($this->getSortColumn())->getType()) {
            foreach($grid->getColumn($this->getSortColumn())->getIdentifiers() as $identifier){
                if(count($qb->getDQLPart('orderBy')) == 0) {
                    $method = 'orderBy';
                } else {
                    $method = 'addOrderBy';
                }

                $qb->{$method}($identifier, $this->getSortDirect());
            }
        } else {
            $qb->orderBy($grid->getColumn($this->getSortColumn())->getIdentifier(), $this->getSortDirect());
        }

        $offset = $grid->getRecordsPerPage() * $this->getNumberOfCurrentPage() - $grid->getRecordsPerPage();

        if($offset < 0) {
            $offset = 0;
        }

        $qb->setMaxResults($grid->getRecordsPerPage());
        $qb->setFirstResult($offset);

        $result = $qb->getQuery()->getArrayResult();

        $this->_records = count($resultCount);
        $this->_recordsFiltered = count($result);

        return $result;
    }

    private function _mapRelations($alias, $aliases, $relations = null, $sub = false)
    {

        if(count($aliases) == 0) {
            return array();
        }

        foreach($aliases[$alias] as $item)
        {
            if($sub == false) {
                $relations[$item['alias']] = $item['relation'];
            } else {
                $relations[$item['alias']] = $relations[$alias] . '/' . $item['relation'];
            }

            if(isset($aliases[$item['alias']])) {
                $rel = $this->_mapRelation($item['alias'], $aliases, $relations, true);

                foreach($rel as $key => $value) {
                    $relations[$key] = $value;
                }
            }
        }

        return $relations;
    }

    private function _getRowData($colName, $item, $relations)
    {
        // Rozlozime nazev sloupce
        $explode = explode('.', $colName);

        // Urcime nazev sloupce a nazev aliasu relace
        if(isset($explode[1])) {
            $name = $explode[1];
            $relationAlias = $explode[0];
        } else {
            $name = $explode[0];
            $relationAlias = null;
        }

        // Pokud se data nachazeji na zakladni urovni
        if(isset($item[$name]) && ($relationAlias == null || !isset($relations[$relationAlias]))) {

            return $item[$name];
        }

        if($relationAlias == null && isset($item['translations'])) {
            $string = null;
            foreach($item['translations'] as $translation) {
                if(isset($translation[$name])) {
                    $string .= '<strong>' . $translation['lang'] . ':</strong> ' . $translation[$name] . ' <br/>';
                }
            }
            return $string;
        }

        // Pokud existuje alias relace mezi relacemi
        if(array_key_exists($relationAlias, $relations))
        {
            // Rozlozime cestu k polozce v relaci
            $relation = explode('/', $relations[$relationAlias]);
            $itemRelation = $item;

            // Nacteme data z relace
            foreach ($relation as $rel) {
                if(count($itemRelation[$rel]) == 1 && isset($itemRelation[$rel][0])) {
                    $itemRelation = $itemRelation[$rel][0];
                } else {
                    $itemRelation = $itemRelation[$rel];
                }
            }

            // Pokud se jedna o relaci s preklady
            if($rel == 'translations') {
                $string = null;

                if(!isset($itemRelation[0])) { $it = $itemRelation; unset($itemRelation); $itemRelation[0] = $it; }
                foreach($itemRelation as $translation) {
                    if(isset($translation[$name])) {
                        $string .= '<strong>' . $translation['lang'] . ':</strong> ' . $translation[$name] . ' <br/>';
                    }
                }

                return $string;
            } else {
                if(isset($itemRelation[$name])) {
                    return $itemRelation[$name];
                } else {
                    return null;
                }
            }

            return '';
        }

        return '';
    }

    /**
     * Set instance of Doctrine QueryBuilder
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @return Doctrine2
     */
    public function setQueryBuilder(\Doctrine\ORM\QueryBuilder $queryBuilder)
    {
        $this->_queryBuilder = $queryBuilder;

        return $this;
    }

    /**
     * Return instance of Doctrine QueryBuilder
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->_queryBuilder;
    }
}
