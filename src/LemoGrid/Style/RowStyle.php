<?php

namespace LemoGrid\Style;

use LemoGrid\Column\ColumnCondition;
use LemoGrid\Exception;
use Traversable;
use Laminas\Stdlib\AbstractOptions;

class RowStyle extends AbstractOptions
{
    /**
     * @var ColumnCondition[]
     */
    protected $conditions = [];

    /**
     * @var Property[]
     */
    protected $properties = [];

    /**
     * @param  array|ColumnCondition $condition
     * @return RowStyle
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
     * @return RowStyle
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
     * @return RowStyle
     */
    public function clearConditions()
    {
        $this->conditions = [];

        return $this;
    }

    /**
     * @param  array|Property $property
     * @return RowStyle
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
     * Set styles for an column.
     *
     * @param  array|Traversable $properties
     * @return RowStyle
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
     * Get defined styles
     *
     * @return Property[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Clear styles
     *
     * @return RowStyle
     */
    public function clearProperties()
    {
        $this->properties = [];

        return $this;
    }
}