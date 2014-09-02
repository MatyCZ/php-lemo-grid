<?php

namespace LemoGrid\Column;

use LemoGrid\Adapter\AdapterInterface;
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
     * Get conditions for a column
     *
     * @param  array|Traversable|AbstractOptions $conditions
     * @return ColumnInterface
     */
    public function setConditions(array $conditions);

    /**
     * Retrieve conditions for a column
     *
     * @return AbstractOptions
     */
    public function getConditions();

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
     * @param  AdapterInterface $adapter
     * @param  array            $item
     * @return string|int
     */
    public function renderValue(AdapterInterface $adapter, array $item);
}
