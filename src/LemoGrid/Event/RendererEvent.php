<?php

namespace LemoGrid\Event;

use LemoGrid\Adapter\AdapterInterface;
use LemoGrid\GridInterface;
use LemoGrid\ResultSet\ResultSetInterface;
use Laminas\EventManager\Event;

class RendererEvent extends Event
{
    /**
     * List of events
     */
    const EVENT_RENDER       = 'lemoGrid.renderer.render';
    const EVENT_RENDER_DATA  = 'lemoGrid.renderer.renderData';

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
     * @return RendererEvent
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Get adapter object
     *
     * @return null|AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param  GridInterface $grid
     * @return RendererEvent
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
     * @return RendererEvent
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
