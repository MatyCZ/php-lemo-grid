<?php

namespace LemoGrid;

use LemoGrid\ColumnAttributeRemovalInterface;
use LemoGrid\ColumnInterface;
use LemoGrid\Column\ColumnAttributes;
use LemoGrid\Exception;
use Traversable;
use Zend\Stdlib\AbstractOptions;
use Zend\Stdlib\InitializableInterface;

class Column implements
    ColumnAttributeRemovalInterface,
    ColumnInterface,
    InitializableInterface
{
    /**
     * @var array
     */
    protected $attributes = array();

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param  null|int|string $name    Optional name for the column
     * @param  array $options Optional options for the column
     * @return \LemoGrid\Column
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
     * Set options for an column. Accepted options are:
     * - label: label to associate with the column
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

        if (isset($options['name'])) {
            $this->setName($options['name']);
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
     * @return null|mixed
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
     * Set column attributes
     *
     * @param  array|\Traversable|ColumnAttributes $attributes
     * @throws Exception\InvalidArgumentException
     * @return Column
     */
    public function setAttributes($attributes)
    {
        if (!$attributes instanceof ColumnAttributes) {
            if (is_object($attributes) && !$attributes instanceof Traversable) {
                throw new Exception\InvalidArgumentException(sprintf(
                        'Expected instance of LemoGrid\Column\ColumnAttributes; '
                            . 'received "%s"', get_class($attributes))
                );
            }

            $attributes = new ColumnAttributes($attributes);
        }

        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Get column attributes
     *
     * @return ColumnAttributes
     */
    public function getAttributes()
    {
        if (!$this->attributes) {
            $this->setAttributes(new ColumnAttributes());
        }

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
     * Set the column identifier
     *
     * @param  string $identifier
     * @return Column
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get the column identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        if(null === $this->identifier) {
            $this->identifier = $this->getName();
        }

        return $this->identifier;
    }

    /**
     * Set the column name
     *
     * @param  string $name
     * @return Column
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->getAttributes()->setName($name);

        return $this;
    }

    /**
     * Get the column name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the column value
     *
     * @param string $value
     * @return Column
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the column value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    public function renderValue()
    {
        return $this->getValue();
    }
}
