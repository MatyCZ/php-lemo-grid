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
    protected $separator = ' ';

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
     * @param  string $separator
     * @return ConcatOptions
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
