<?php

namespace LemoGrid;

interface ColumnInterface
{
    /**
     * Set the name of this column
     *
     * In most cases, this will proxy to the attributes for storage, but is
     * present to indicate that columns are generally named.
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
     * Set options for an column
     *
     * @param  array|\Traversable $options
     * @return ColumnInterface
     */
    public function setOptions($options);

    /**
     * get the defined options
     *
     * @return array
     */
    public function getOptions();

    /**
     * Set a single column attribute
     *
     * @param  string $key
     * @param  mixed $value
     * @return ColumnInterface
     */
    public function setAttribute($key, $value);

    /**
     * Retrieve a single column attribute
     *
     * @param  string $key
     * @return mixed
     */
    public function getAttribute($key);

    /**
     * Return true if a specific attribute is set
     *
     * @param  string $key
     * @return bool
     */
    public function hasAttribute($key);

    /**
     * Set many attributes at once
     *
     * Implementation will decide if this will overwrite or merge.
     *
     * @param  array|\Traversable $arrayOrTraversable
     * @return ColumnInterface
     */
    public function setAttributes($arrayOrTraversable);

    /**
     * Retrieve all attributes at once
     *
     * @return array|\Traversable
     */
    public function getAttributes();

    /**
     * Set the value of the column
     *
     * @param  mixed $value
     * @return ColumnInterface
     */
    public function setValue($value);

    /**
     * Retrieve the column value
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Set the label (if any) used for this column
     *
     * @param  $label
     * @return ColumnInterface
     */
    public function setLabel($label);

    /**
     * Retrieve the label (if any) used for this column
     *
     * @return string
     */
    public function getLabel();
}
