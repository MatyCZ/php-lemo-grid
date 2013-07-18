<?php

namespace LemoGrid\Column;

use LemoGrid\Exception;
use Traversable;

class ConcatGroup extends AbstractColumn
{
    /**
     * Column options
     *
     * @var ConcatGroupOptions
     */
    protected $options;

    /**
     * @param null|string                        $name
     * @param array|Traversable|ConcatGroupOptions    $options
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
     * @param  array|\Traversable|ConcatGroupOptions $options
     * @throws Exception\InvalidArgumentException
     * @return ConcatGroup
     */
    public function setOptions($options)
    {
        if (!$options instanceof ConcatGroupOptions) {
            if (is_object($options) && !$options instanceof Traversable) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Expected instance of LemoGrid\Column\ConcatGroupOptions; '
                    . 'received "%s"', get_class($options))
                );
            }

            $options = new ConcatGroupOptions($options);
        }

        $this->options = $options;

        return $this;
    }

    /**
     * Get column options
     *
     * @return ConcatGroupOptions
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new ConcatGroupOptions());
        }

        return $this->options;
    }
}
