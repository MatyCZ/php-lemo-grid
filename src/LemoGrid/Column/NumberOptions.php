<?php

namespace LemoGrid\Column;

use Zend\Stdlib\AbstractOptions;

class NumberOptions extends AbstractOptions
{
    /**
     * @var int
     */
    protected $multiplier;

    /**
     * @var int
     */
    protected $divisor;

    /**
     * @param  int $divisor
     * @return NumberOptions
     */
    public function setDivisor($divisor)
    {
        $this->divisor = $divisor;

        return $this;
    }

    /**
     * @return int
     */
    public function getDivisor()
    {
        return $this->divisor;
    }

    /**
     * @param  int $multiplier
     * @return NumberOptions
     */
    public function setMultiplier($multiplier)
    {
        $this->multiplier = $multiplier;

        return $this;
    }

    /**
     * @return int
     */
    public function getMultiplier()
    {
        return $this->multiplier;
    }
}
