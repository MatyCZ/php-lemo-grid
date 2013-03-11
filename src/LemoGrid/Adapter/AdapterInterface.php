<?php

namespace LemoGrid\Adapter;

use LemoGrid\Collection\Data;

interface AdapterInterface
{
    /**
     * Return data from adapter
     *
     * @return Data
     */
    public function getData();
}
