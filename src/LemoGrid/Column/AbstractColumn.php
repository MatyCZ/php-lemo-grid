<?php

namespace LemoGrid\Column;

use Zend\Stdlib\ArrayUtils;
use LemoGrid\Exception;
use Traversable;
use Zend\Stdlib\AbstractOptions;
use Zend\Stdlib\InitializableInterface;

abstract class AbstractColumn implements
    ColumnInterface,
    InitializableInterface
{
    /**
     * @var ColumnAttributes
     */
    protected $attributes;

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
     * @return AbstractColumn
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
     * @return AbstractColumn|ColumnInterface
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
     * Set column attributes
     *
     * @param  array|\Traversable|ColumnAttributes $attributes
     * @throws Exception\InvalidArgumentException
     * @return AbstractColumn
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
     * Clear all attributes
     *
     * @return AbstractColumn|ColumnInterface
     */
    public function clearAttributes()
    {
        $this->attributes = new ColumnAttributes();
        return $this;
    }

    /**
     * Set the column identifier
     *
     * @param  string $identifier
     * @return AbstractColumn
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
     * @return AbstractColumn
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * @return AbstractColumn
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
