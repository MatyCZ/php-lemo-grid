<?php

namespace Lemo\Grid\Column;

use Lemo\Grid\Adapter\AdapterInterface;
use Lemo\Grid\Exception;
use Traversable;

class ButtonLink extends AbstractColumn
{
    /**
     * Column options
     *
     * @var ButtonLinkOptions
     */
    protected $options;

    /**
     * Attributes valid for the button tag
     *
     * @var array
     */
    protected $validTagAttributes = [
        'class' => true,
        'disabled' => true,
        'href' => true,
        'id' => true,
        'value' => true,
    ];

    /**
     * Set column options
     *
     * @param  array|\Traversable|ButtonLinkOptions $options
     * @throws Exception\InvalidArgumentException
     * @return ButtonLink
     */
    public function setOptions($options)
    {
        if (!$options instanceof ButtonLinkOptions) {
            if (is_object($options) && !$options instanceof Traversable) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Expected instance of Lemo\Grid\Column\ButtonLinkOptions; '
                    . 'received "%s"', get_class($options))
                );
            }

            $options = new ButtonLinkOptions($options);
        }

        $this->options = $options;

        return $this;
    }

    /**
     * Get column options
     *
     * @return ButtonLinkOptions
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new ButtonLinkOptions());
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
        return $this->openTag() . $this->getOptions()->getValue() . $this->closeTag();
    }

    /**
     * Generate an opening button tag
     *
     * @return string
     */
    public function openTag()
    {
        $attributes = $this->getOptions()->getAttributes();

        if (null === $this->getAttributes()) {
            return '<a>';
        }

        $attributes = $this->createAttributesString($attributes);
        return sprintf('<a %s>', $attributes);
    }

    /**
     * Return a closing button tag
     *
     * @return string
     */
    public function closeTag()
    {
        return '</a>';
    }
}
