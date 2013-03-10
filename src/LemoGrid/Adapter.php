<?php

namespace LemoGrid;

class Adapter
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
     * Sort column
     *
     * @var string
     */
    protected $sortColumn;

    /**
     * Sort direct
     *
     * @var string
     */
    protected $sortDirect;

    /**
     * Return sort by column index
     *
     * @return string
     */
    public function getSortColumn()
    {
        if(null === $this->sortColumn) {
            $queryParams = $this->getGrid()->getQueryParams();

            if(isset($queryParams['sidx'])) {
                $this->sortColumn = $queryParams['sidx'];
            } else {
                $this->sortColumn = $this->getGrid()->getDefaultSortColumn();
            }
        }

        return $this->sortColumn;
    }

    /**
     * Return sort direct
     *
     * @return string
     */
    public function getSortDirect()
    {
        if(null === $this->sortDirect) {
            $queryParams = $this->getGrid()->getQueryParams();
            if(isset($queryParams['sidx'])) {
                if(isset($queryParams['sord'])) {
                    if(strtolower($queryParams['sord']) != 'asc' AND strtolower($queryParams['sord']) != 'desc') {
                        throw new Exception\UnexpectedValueException('Sort direct must be ' . 'asc' . ' or ' . 'desc' . '!');
                    }

                    $this->sortDirect = $queryParams['sord'];
                } else {
                    $this->sortDirect = 'asc';
                }
            } else {
                $this->sortDirect = $this->getGrid()->getDefaultSortOrder();
            }
        }

        return $this->sortDirect;
    }

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
