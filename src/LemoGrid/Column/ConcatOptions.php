<?php

namespace LemoGrid\Column;

use Laminas\Stdlib\AbstractOptions;

class ConcatOptions extends AbstractOptions
{
    /**
     * @var array
     */
    protected $identifiers = [];

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
