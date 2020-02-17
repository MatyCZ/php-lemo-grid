<?php

namespace LemoGrid\Column;

use Laminas\Stdlib\AbstractOptions;

class ButtonLinkOptions extends AbstractOptions
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
     * @return ButtonLinkOptions
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
     * @return ButtonLinkOptions
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
