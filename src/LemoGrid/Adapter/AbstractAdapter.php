<?php

namespace LemoGrid\Adapter;

use LemoGrid\GridInterface;
use LemoGrid\ResultSet\Data;

abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * Number of filtered items
     *
     * @var int
     */
    protected $countItems = 0;

    /**
     * Number of items
     *
     * @var int
     */
    protected $countItemsTotal = 0;

    /**
     * @var Data
     */
    protected $data;

    /**
     * @var GridInterface
     */
    protected $grid;

    /**
     * Get number of current page
     *
     * @return int
     */
    public function getNumberOfPages()
    {
        return ceil($this->getCountOfItemsTotal() / $this->getGrid()->getOptions()->getRecordsPerPage());
    }

    /**
     * Get number of current page
     *
     * @return int
     */
    public function getNumberOfCurrentPage()
    {
        $page = $this->getGrid()->getParam('page');

        if(null === $page) {
            $page = 1;
        }

        return $page;
    }

    /**
     * Return count of items
     *
     * @return int
     */
    public function getCountOfItems()
    {
        return $this->countItems;
    }

    /**
     * Return count of items total
     *
     * @return int
     */
    public function getCountOfItemsTotal()
    {
        return $this->countItemsTotal;
    }

    /**
     * @param  Data $data
     * @return AbstractAdapter
     */
    public function setData(Data $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return Data
     */
    public function getData()
    {
        if(null === $this->data) {
            $this->data = new Data();

            $this->populateData();
        }

        return $this->data;
    }

    /**
     * Set grid instance
     *
     * @param  GridInterface $grid
     * @return AbstractAdapter
     */
    public function setGrid(GridInterface $grid)
    {
        $this->grid = $grid;

        return $this;
    }

    /**
     * Get grid instance
     *
     * @return GridInterface
     */
    public function getGrid()
    {
        return $this->grid;
    }
}
