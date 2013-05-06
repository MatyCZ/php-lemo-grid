<?php

namespace LemoGrid;

use Zend\Stdlib\AbstractOptions;
use Traversable;

interface ColumnInterface
{
    /**
     * Set the column name
     *
     * @param  string $name
     * @return ColumnInterface
     */
    public function setName($name);

    /**
     * Retrieve the column name
     *
     * @return string
     */
    public function getName();

    /**
     * Set the column identifier
     *
     * @param  string $identifier
     * @return string
     */
    public function setIdentifier($identifier);

    /**
     * Retrieve the column identifier
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Set options for a column
     *
     * @param  array|Traversable|AbstractOptions $options
     * @return ColumnInterface
     */
    public function setOptions($options);

    /**
     * Retrieve options for a column
     *
     * @return AbstractOptions
     */
    public function getOptions();

    /**
     * Get attributes for a column
     *
     * @param  array|Traversable|AbstractOptions $attributes
     * @return ColumnInterface
     */
    public function setAttributes($attributes);

    /**
     * Retrieve attributes for a column
     *
     * @return AbstractOptions
     */
    public function getAttributes();

    /**
     * Set the value of the column
     *
     * @param  string $value
     * @return ColumnInterface
     */
    public function setValue($value);

    /**
     * Retrieve the column value
     *
     * @return string
     */
    public function getValue();

    /**
     * Compose the column value
     *
     * @return string
     */
    public function renderValue();
}
