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
     * Return sort by column index
     *
     * @return string
     */
    public function getSortColumn();

    /**
     * Return sort direct
     *
     * @throws Exception\UnexpectedValueException
     * @return string
     */
    public function getSortDirect();
}
