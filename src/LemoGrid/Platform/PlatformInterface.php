<?php

namespace LemoGrid\Platform;

use LemoGrid\Exception;
use Traversable;
use Zend\Stdlib\AbstractOptions;

interface PlatformInterface
{
    /**
     * Set options for a column
     *
     * @param  array|Traversable|AbstractOptions $options
     * @return PlatformInterface
     */
    public function setOptions($options);

    /**
     * Retrieve options for a column
     *
     * @return AbstractOptions
     */
    public function getOptions();

    /**
     * Is the grid rendered?
     *
     * @return bool
     */
    public function isRendered();

    /**
     * Return converted filter operator
     *
     * @param  string $operator
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    public function getFilterOperator($operator);

    /**
     * Return sort by column name => direct
     *
     * @return array
     */
    public function getSort();
}
