<?php

namespace Lemo\Grid\Column;

use Lemo\Grid\Adapter\AdapterInterface;
use Lemo\Grid\Exception;
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
                    'Expected instance of Lemo\Grid\Column\NumberOptions; '
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

    /**
     * @param  AdapterInterface $adapter
     * @param  array            $item
     * @return string
     */
    public function renderValue(AdapterInterface $adapter, array $item)
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
