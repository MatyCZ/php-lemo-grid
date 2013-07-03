<?php

namespace LemoGrid\Column;

use Zend\Stdlib\AbstractOptions;

class ConcatOptions extends AbstractOptions
{
    /**
     * @var array
     */
    protected $identifiers = array();

    /**
     * @var string
     */
    protected $pattern;

    /**
     * @param  array $identifiers
     * @return ConcatOptions
     */
    public function setIdentifiers(array $identifiers)
    {
        $this->identifiers = $identifiers;
        return $this;
    }

    /**
     * @return array
     */
    public function getIdentifiers()
    {
        return $this->identifiers;
    }

    /**
     * @param  string $pattern
     * @return ConcatOptions
     */
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;
        return $this;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }
}
