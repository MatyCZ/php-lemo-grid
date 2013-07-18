<?php

namespace LemoGrid\Column;

use Zend\Stdlib\AbstractOptions;

class ConcatGroupOptions extends AbstractOptions
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
     * @var string
     */
    protected $separator = PHP_EOL;

    /**
     * @param  array $identifiers
     * @return ConcatGroupOptions
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
     * @return ConcatGroupOptions
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

    /**
     * @param  string $separator
     * @return ConcatGroupOptions
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;
        return $this;
    }

    /**
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }
}
