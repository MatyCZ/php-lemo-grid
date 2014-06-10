<?php

namespace LemoGrid\ResultSet;

use LemoGrid\Exception;

interface ResultSetInterface
{
    /**
     * Return data as array
     *
     * @return array
     */
    public function getArrayCopy();
}
