<?php

namespace LemoGrid\Adapter;

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
     * Get number of current page
     *
     * @return int
     */
    public function getNumberOfPages()
    {
        return ceil($this->getCountOfItemsTotal() / $this->getGrid()->getRecordsPerPage());
    }

    /**
     * Get number of current page
     *
     * @return int
     */
    public function getNumberOfCurrentPage()
    {
        $page = $this->getGrid()->getQueryParam('page');

        if(null === $page) {
            $page = $this->getGrid()->getDefaultPage();
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
}
