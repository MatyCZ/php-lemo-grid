<?php

namespace Lemo\Grid\Event;

use Lemo\Grid\Adapter\AdapterInterface;
use Lemo\Grid\GridInterface;
use Lemo\Grid\ResultSet\ResultSetInterface;
use Laminas\EventManager\Event;

class AdapterEvent extends Event
{
    /**
     * List of events
     */
    const EVENT_FETCH_DATA  = 'lemoGrid.adapter.loadData';

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var GridInterface
     */
    protected $grid;

    /**
     * @var ResultSetInterface
     */
    protected $resultSet;

    /**
     * Set adapter object to compose in this event
     *
     * @param  AdapterInterface $adapter
     * @return AdapterEvent
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Get adapter object
     *
     * @return null|object
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param  GridInterface $grid
     * @return AdapterEvent
     */
    public function setGrid(GridInterface $grid)
    {
        $this->grid = $grid;

        return $this;
    }

    /**
     * @return GridInterface
     */
    public function getGrid()
    {
        return $this->grid;
    }

    /**
     * @param  ResultSetInterface $resultSet
     * @return AdapterEvent
     */
    public function setResultSet(ResultSetInterface $resultSet)
    {
        $this->resultSet = $resultSet;

        return $this;
    }

    /**
     * @return ResultSetInterface
     */
    public function getResultSet()
    {
        return $this->resultSet;
    }
}
