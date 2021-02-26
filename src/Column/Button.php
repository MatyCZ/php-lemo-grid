<?php

namespace Lemo\Grid\Column;

use Lemo\Grid\Adapter\AdapterInterface;
use Lemo\Grid\Exception;
use Traversable;

class Button extends AbstractColumn
{
    /**
     * Column options
     *
     * @var ButtonOptions
     */
    protected $options;

    /**
     * Attributes valid for the button tag
     *
     * @var array
     */
    protected $validTagAttributes = [
        'name'           => true,
        'autofocus'      => true,
        'disabled'       => true,
        'form'           => true,
        'formaction'     => true,
        'formenctype'    => true,
        'formmethod'     => true,
        'formnovalidate' => true,
        'formtarget'     => true,
        'type'           => true,
        'value'          => true,
    ];

    /**
     * Set column options
     *
     * @param  array|\Traversable|ButtonOptions $options
     * @throws Exception\InvalidArgumentException
     * @return Button
     */
    public function setOptions($options)
    {
        if (!$options instanceof ButtonOptions) {
            if (is_object($options) && !$options instanceof Traversable) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Expected instance of Lemo\Grid\Column\ButtonOptions; '
                    . 'received "%s"', get_class($options))
                );
            }

            $options = new ButtonOptions($options);
        }

        $this->options = $options;

        return $this;
    }

    /**
     * Get column options
     *
     * @return ButtonOptions
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new ButtonOptions());
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
            return '<button>';
        }

        $attributes = $this->createAttributesString($attributes);
        return sprintf('<button %s>', $attributes);
    }

    /**
     * Return a closing button tag
     *
     * @return string
     */
    public function closeTag()
    {
        return '</button>';
    }
}
