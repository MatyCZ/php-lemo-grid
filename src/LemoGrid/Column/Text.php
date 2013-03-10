<?php

namespace LemoGrid\Column;

use LemoGrid\Exception;
use Traversable;

class Text extends AbstractColumn
{
    /**
     * Column options
     *
     * @var TextOptions
     */
    protected $options;

    /**
     * @param null|string                        $name
     * @param array|Traversable|TextOptions      $options
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
     * @param  array|\Traversable|TextOptions $options
     * @throws Exception\InvalidArgumentException
     * @return Text
     */
    public function setOptions($options)
    {
        if (!$options instanceof TextOptions) {
            if (is_object($options) && !$options instanceof Traversable) {
                throw new Exception\InvalidArgumentException(sprintf(
                        'Expected instance of LemoGrid\Column\TextOptions; '
                            . 'received "%s"', get_class($options))
                );
            }

            $options = new TextOptions($options);
        }

        $this->options = $options;

        return $this;
    }

    /**
     * Get column options
     *
     * @return TextOptions
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new TextOptions());
        }

        return $this->options;
    }
}
