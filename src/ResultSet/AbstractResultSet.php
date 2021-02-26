<?php

namespace Lemo\Grid\ResultSet;

abstract class AbstractResultSet implements ResultSetInterface
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @param  array $data
     * @return AbstractResultSet
     */
    public function setData(array $data)
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
}