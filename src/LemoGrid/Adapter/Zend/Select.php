<?php

namespace LemoGrid\Adapter\Zend;

use LemoGrid\Adapter\AbstractAdapter;
use Zend\Db\Adapter as DbAdapter;
use Zend\Db\Sql\Select as DbSelect;
use Zend\Db\Sql\Where;

class Select extends AbstractAdapter
{
    /**
     * @var DbAdapter
     */
    protected $adapter;

    /**
     * @var DbSelect
     */
    protected $select;

    /**
     * @return array
     */
    public function getData()
    {
        $grid = $this->getGrid();
        $data = array();

        if(null === $this->getSelect()) {
            throw new \Exception("No Select instance given");
        }

        foreach ($this->_executeQuery() as $item)
        {
            $rowData = array();

            foreach($grid->getColumns() as $column)
            {
                $colName = $column->getName();
                $rowData[$colName] = null;

                if(isset($item[$colName])) {
                    $value = $item[$colName];
                } else {
                    $value = '';
                }

                // Nacteme si data radku
                $value = $column->renderValue($value);

                if('concat' == $column->getType()) {
                    $values = array();
                    foreach($column->getIdentifiers() as $identifier => $name) {
                        $val = $item[$name];

                        if(!empty($val)) {
                            $values[] = $val;
                        }
                    }

                    $value = implode($column->getSeparator(), $values);

                    unset($values, $name);
                }

                // Projdeme data a nahradime data ve formatu %xxx%
                if(preg_match_all('/%([a-zA-Z0-9\._-]+)%/', $value, $matches)) {
                    foreach($matches[0] as $key => $match) {
                        $value = str_replace($matches[0][$key], $item[$matches[1][$key]], $value);
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

        $select = $this->getSelect();
        $whereFromSelect = $select->getRawState('where');

        // WHERE
        foreach($grid->getColumns() as $col)
        {
            if(true === $col->getIsSearchable())
            {
                $prepend = null;
                $append = null;

                if($grid->getParam($col->getName()))
                {
                    if('concat' == $col->getType()) {
                        $where = new Where();
                        foreach($col->getIdentifiers() as $identifier){
                            $where->like($identifier, '%' . $grid->getParam($col->getName()) . '%');
                            $where->andPredicate($whereFromSelect);
                            $where->or;
                        }
                        $select->where($where);
                    } else {
                        if('text' == $col->getSearchElement()) {
                            $prepend = $append = '%';
                        }

                        $select->where($col->getIdentifier() . " LIKE '" . $prepend . $grid->getParam($col->getName()) . $append . "'");
                    }
                }
            }
        }

        // ORDER
        if('concat' == $grid->getColumn($this->getSortColumn())->getType()) {
            foreach($grid->getColumn($this->getSortColumn())->getIdentifiers() as $identifier => $name){
                $select->order($identifier . ' ' . $this->getSortDirect());
            }
        } else {
            $select->order($grid->getColumn($this->getSortColumn())->getIdentifier() . ' ' . $this->getSortDirect());
        }

        $select = $select->getSqlString($this->getAdapter()->getPlatform());
        $offset = $grid->getRecordsPerPage() * $this->getNumberOfCurrentPage() - $grid->getRecordsPerPage();

        if($offset < 0) {
            $offset = 0;
        }

        $rows = array();
        foreach($this->getAdapter()->query($select . ' LIMIT ' . $grid->getRecordsPerPage() . ' OFFSET ' . $offset)->execute() as $row) {
            $rows[] = $row;
        }

        $this->_records = $this->getAdapter()->query($select)->execute()->count();
        $this->_recordsFiltered = count($rows);

        return $rows;
    }

    /**
     * Set adapter
     *
     * @param  DbAdapter $adapter
     * @return Select
     */
    public function setAdapter(DbAdapter $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Get adapter
     *
     * @return DbAdapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Set instance of Select
     *
     * @param DbSelect $select
     * @return Select
     */
    public function setSelect(DbSelect $select)
    {
        $this->select = $select;

        return $this;
    }

    /**
     * Return instance of Select
     *
     * @return DbSelect
     */
    public function getSelect()
    {
        return $this->select;
    }
}
