<?php

namespace LemoGrid\Event;

use Zend\EventManager\Event;

class AdapterEvent extends Event
{
    /**
     * List of events
     */
    const EVENT_LOAD_DATA  = 'loadData';

    /**
     * @var mixed
     */
    protected $adapter;

    /**
     * @var string
     */
    protected $adapterName;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var string
     */
    protected $gridName;

    /**
     * Set adapter object to compose in this event
     *
     * @param  object $adapter
     * @throws Exception\InvalidArgumentException
     * @return AdapterEvent
     */
    public function setAdapter($adapter)
    {
        if (!is_object($adapter)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a adapter object as an argument; %s provided'
                ,__METHOD__, gettype($adapter)
            ));
        }
        // Performance tweak, don't add it as param.
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
     * Set the name of a given adapter
     *
     * @param  string $adapterName
     * @throws Exception\InvalidArgumentException
     * @return AdapterEvent
     */
    public function setAdapterName($adapterName)
    {
        if (!is_string($adapterName)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a string as an argument; %s provided'
                ,__METHOD__, gettype($adapterName)
            ));
        }
        // Performance tweak, don't add it as param.
        $this->adapterName = $adapterName;

        return $this;
    }

    /**
     * Get the name of a given adapter
     *
     * @return string
     */
    public function getAdapterName()
    {
        return $this->adapterName;
    }

    /**
     * @param  array $data
     * @return AdapterEvent
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the name of a given grid
     *
     * @param  string $gridName
     * @throws Exception\InvalidArgumentException
     * @return GridEvent
     */
    public function setGridName($gridName)
    {
        if (!is_string($gridName)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a string as an argument; %s provided'
                ,__METHOD__, gettype($gridName)
            ));
        }
        // Performance tweak, don't add it as param.
        $this->gridName = $gridName;

        return $this;
    }

    /**
     * Get the name of a given grid
     *
     * @return string
     */
    public function getGridName()
    {
        return $this->gridName;
    }
}
