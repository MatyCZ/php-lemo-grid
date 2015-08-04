<?php

namespace LemoGrid\Style;

use LemoGrid\Column\ColumnCondition;
use LemoGrid\Exception;
use Traversable;
use Zend\Stdlib\AbstractOptions;

class ColumnStyle extends AbstractOptions
{
    /**
     * Name of column
     *
     * @var string
     */
    protected $column;

    /**
     * @var ColumnCondition[]
     */
    protected $conditions = array();

    /**
     * @var Property[]
     */
    protected $properties = array();

    /**
     * @param  string $column
     * @return ColumnStyle
     */
    public function setColumn($column)
    {
        $this->column = $column;

        return $this;
    }

    /**
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @param  array|ColumnCondition $condition
     * @return ColumnStyle
     */
    public function addCondition($condition)
    {
        if ($condition instanceof ColumnCondition) {
            $this->conditions[] = $condition;
        } elseif (is_array($condition)) {
            $this->conditions[] = new ColumnCondition($condition);
        } else {
            throw new Exception\InvalidArgumentException(
                'The conditions parameter must be an array or array of ColumnCondition'
            );
        }

        return $this;
    }

    /**
     * Set conditions for an column.
     *
     * @param  array|Traversable $conditions
     * @return ColumnStyle
     * @throws Exception\InvalidArgumentException
     */
    public function setConditions(array $conditions)
    {
        foreach ($conditions as $condition) {
            $this->addCondition($condition);
        }

        return $this;
    }

    /**
     * Get defined conditions
     *
     * @return ColumnCondition[]
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * Clear conditions
     *
     * @return ColumnStyle
     */
    public function clearConditions()
    {
        $this->conditions = array();

        return $this;
    }

    /**
     * @param  array|Property $property
     * @return ColumnStyle
     */
    public function addProperty($property)
    {
        if ($property instanceof Property) {
            $this->properties[] = $property;
        } elseif (is_array($property)) {
            $this->properties[] = new Property($property);
        } else {
            throw new Exception\InvalidArgumentException(
                'The styles parameter must be an array or array of Property'
            );
        }

        return $this;
    }

    /**
     * Set properties for an column.
     *
     * @param  array|Traversable $properties
     * @return ColumnStyle
     * @throws Exception\InvalidArgumentException
     */
    public function setProperties(array $properties)
    {
        foreach ($properties as $property) {
            $this->addProperty($property);
        }

        return $this;
    }

    /**
     * Get defined properties
     *
     * @return Property[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Clear properties
     *
     * @return ColumnStyle
     */
    public function clearProperties()
    {
        $this->properties = array();

        return $this;
    }
}