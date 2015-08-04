<?php

namespace LemoGrid\ResultSet;

use LemoGrid\Exception;

interface ResultSetInterface
{
    /**
     * Set data
     *
     * @param  array $data
     * @return array
     */
    public function setData(array $data);

    /**
     * Get data
     *
     * @return array
     */
    public function getData();
}
