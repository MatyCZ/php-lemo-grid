<?php

namespace LemoGrid\Column;

use LemoGrid\Column;
use LemoGrid\Column\ConcatOptions;
use LemoGrid\Exception;
use Traversable;

class Concat extends Column
{
    /**
     * Column options
     *
     * @var ConcatOptions
     */
    protected $options;

    /**
     * @param null|string                        $name
     * @param array|Traversable|ConcatOptions    $options
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
     * @param  array|\Traversable|ConcatOptions $options
     * @throws Exception\InvalidArgumentException
     * @return Concat
     */
    public function setOptions($options)
    {
        if (!$options instanceof ConcatOptions) {
            if (is_object($options) && !$options instanceof Traversable) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Expected instance of LemoGrid\Column\ConcatOptions; '
                    . 'received "%s"', get_class($options))
                );
            }

            $options = new ConcatOptions($options);
        }

        $this->options = $options;

        return $this;
    }

    /**
     * Get column options
     *
     * @return ConcatOptions
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new ConcatOptions());
        }

        return $this->options;
    }
}
