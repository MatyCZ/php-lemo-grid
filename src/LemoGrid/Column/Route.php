<?php

namespace LemoGrid\Column;

use LemoGrid\Exception;
use Traversable;

class Route extends AbstractColumn
{
    /**
     * Column options
     *
     * @var RouteOptions
     */
    protected $options;

    /**
     * @param null|string                        $name
     * @param array|Traversable|RouteOptions       $options
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
     * @param  array|\Traversable|RouteOptions $options
     * @throws Exception\InvalidArgumentException
     * @return Route
     */
    public function setOptions($options)
    {
        if (!$options instanceof RouteOptions) {
            if (is_object($options) && !$options instanceof Traversable) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Expected instance of LemoGrid\Column\RouteOptions; '
                    . 'received "%s"', get_class($options))
                );
            }

            $options = new RouteOptions($options);
        }

        $this->options = $options;

        return $this;
    }

    /**
     * Get column options
     *
     * @return RouteOptions
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new RouteOptions());
        }

        return $this->options;
    }

    public function composeValue()
    {
        return $this->getValue();
    }
}
