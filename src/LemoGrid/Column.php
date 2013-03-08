<?php

namespace LemoGrid;

use Traversable;
use Zend\Stdlib\ArrayUtils;

class Column implements
    ColumnInterface,
    ColumnAttributeRemovalInterface
{
    /**
     * @var array
     */
    protected $attributes = array();

    /**
     * @var string
     */
    protected $label;

    /**
     * @var array custom options
     */
    protected $options = array();

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param  null|int|string  $name    Optional name for the column
     * @param  array            $options Optional options for the column
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($name = null, $options = array())
    {
        if (null !== $name) {
            $this->setName($name);
        }

        if (!empty($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * This function is automatically called when creating column with factory. It
     * allows to perform various operations (add columns...)
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Set value for name
     *
     * @param  string $name
     * @return Column|ColumnInterface
     */
    public function setName($name)
    {
        $this->setAttribute('name', $name);
        return $this;
    }

    /**
     * Get value for name
     *
     * @return string|int
     */
    public function getName()
    {
        return $this->getAttribute('name');
    }

    /**
     * Set options for an column. Accepted options are:
     * - label: label to associate with the column
     * - label_attributes: attributes to use when the label is rendered
     *
     * @param  array|Traversable $options
     * @return Column|ColumnInterface
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions($options)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            throw new Exception\InvalidArgumentException(
                'The options parameter must be an array or a Traversable'
            );
        }

        if (isset($options['label'])) {
            $this->setLabel($options['label']);
        }

        if (isset($options['label_attributes'])) {
            $this->setLabelAttributes($options['label_attributes']);
        }

        $this->options = $options;

        return $this;
    }

    /**
     * Get defined options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Return the specified option
     *
     * @param string $option
     * @return NULL|mixed
     */
    public function getOption($option)
    {
        if (!isset($this->options[$option])) {
            return null;
        }

        return $this->options[$option];
    }

    /**
     * Set a single column attribute
     *
     * @param  string $key
     * @param  mixed  $value
     * @return Column|ColumnInterface
     */
    public function setAttribute($key, $value)
    {
        // Do not include the value in the list of attributes
        if ($key === 'value') {
            $this->setValue($value);
            return $this;
        }
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Retrieve a single column attribute
     *
     * @param  $key
     * @return mixed|null
     */
    public function getAttribute($key)
    {
        if (!array_key_exists($key, $this->attributes)) {
            return null;
        }
        return $this->attributes[$key];
    }

    /**
     * Remove a single attribute
     *
     * @param string $key
     * @return ColumnInterface
     */
    public function removeAttribute($key)
    {
        unset($this->attributes[$key]);
        return $this;
    }

    /**
     * Does the column has a specific attribute ?
     *
     * @param  string $key
     * @return bool
     */
    public function hasAttribute($key)
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Set many attributes at once
     *
     * Implementation will decide if this will overwrite or merge.
     *
     * @param  array|Traversable $arrayOrTraversable
     * @return Column|ColumnInterface
     * @throws Exception\InvalidArgumentException
     */
    public function setAttributes($arrayOrTraversable)
    {
        if (!is_array($arrayOrTraversable) && !$arrayOrTraversable instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or Traversable argument; received "%s"',
                __METHOD__,
                (is_object($arrayOrTraversable) ? get_class($arrayOrTraversable) : gettype($arrayOrTraversable))
            ));
        }
        foreach ($arrayOrTraversable as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    /**
     * Retrieve all attributes at once
     *
     * @return array|Traversable
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Remove many attributes at once
     *
     * @param array $keys
     * @return ColumnInterface
     */
    public function removeAttributes(array $keys)
    {
        foreach ($keys as $key) {
            unset($this->attributes[$key]);
        }

        return $this;
    }

    /**
     * Clear all attributes
     *
     * @return Column|ColumnInterface
     */
    public function clearAttributes()
    {
        $this->attributes = array();
        return $this;
    }

    /**
     * Set the column value
     *
     * @param  mixed $value
     * @return Column
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Retrieve the column value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the label used for this column
     *
     * @param $label
     * @return Column|ColumnInterface
     */
    public function setLabel($label)
    {
        if (is_string($label)) {
            $this->label = $label;
        }

        return $this;
    }

    /**
     * Retrieve the label used for this column
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }
}
