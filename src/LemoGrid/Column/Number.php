<?php

namespace LemoGrid\Column;

use LemoGrid\Exception;
use Traversable;

class Number extends AbstractColumn
{
    /**
     * Column options
     *
     * @var NumberOptions
     */
    protected $options;

    /**
     * @param null|string                        $name
     * @param array|Traversable|NumberOptions      $options
     * @param array|Traversable|ColumnAttributes $attributes
     */
    public function __construct($name = null, $options = null, $attributes = null)
    {
        if (null !== $name) {
            $this->setName($name);
        }

        if (null !== $options) {
            $this->setOptions($options);
        }

        if (null !== $attributes) {
            $this->setAttributes($attributes);
        }
    }

    /**
     * Set column options
     *
     * @param  array|\Traversable|NumberOptions $options
     * @throws Exception\InvalidArgumentException
     * @return Number
     */
    public function setOptions($options)
    {
        if (!$options instanceof NumberOptions) {
            if (is_object($options) && !$options instanceof Traversable) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Expected instance of LemoGrid\Column\NumberOptions; '
                    . 'received "%s"', get_class($options))
                );
            }

            $options = new NumberOptions($options);
        }

        $this->options = $options;

        return $this;
    }

    /**
     * Get column options
     *
     * @return NumberOptions
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new NumberOptions());
        }

        return $this->options;
    }

    public function renderValue()
    {
        $value = $this->getValue();

        if (null !== $this->getOptions()->getMultiplier()) {
            $value = round($value * $this->getOptions()->getMultiplier());
        }

        if (null !== $this->getOptions()->getDivisor()) {
            $value = round($value / $this->getOptions()->getDivisor());
        }

        return $value;
    }
}
