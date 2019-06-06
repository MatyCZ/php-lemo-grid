<?php

namespace LemoGrid\Column;

use Zend\Stdlib\AbstractOptions;

class ButtonOptions extends AbstractOptions
{
    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var string
     */
    protected $value;

    /**
     * @param array $params
     * @return ButtonOptions
     */
    public function setAttributes($params)
    {
        $this->attributes = $params;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $value
     * @return ButtonOptions
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
