<?php

namespace LemoGrid\Adapter;

use ArrayAccess;
use Traversable;

interface AdapterOptionsInterface
{
    /**
     * @param  array|ArrayAccess|Traversable $options
     * @return AdapterOptionsInterface
     */
    public function setOptions($options);
}
