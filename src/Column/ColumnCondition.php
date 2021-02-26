<?php

namespace Lemo\Grid\Column;

use Laminas\Stdlib\AbstractOptions;

class ColumnCondition extends AbstractOptions
{
    /**
     * @var string
     */
    protected $column;

    /**
     * @var string
     */
    protected $expression;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param string $column
     * @return $this
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
     * @param string $expression
     * @return $this
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
        return $this;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
