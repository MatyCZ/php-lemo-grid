<?php

namespace LemoGrid\Column;

use DateTime;
use LemoGrid\Adapter\AdapterInterface;
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

    /**
     * @param  AdapterInterface $adapter
     * @param  array            $item
     * @return string
     */
    public function renderValue(AdapterInterface $adapter, array $item)
    {
        $value = null;
        $values = array();

        $valuesLine = array();
        foreach($this->getOptions()->getIdentifiers() as $identifier) {
            $val = $adapter->findValue($identifier, $item);

            if (null !== $val) {
                foreach ($val as $index => $v) {
                    if($v instanceof DateTime) {
                        $v = $v->format('Y-m-d H:i:s');
                    }

                    $valuesLine[$index][] = $v;
                }
            }
        }

        // Slozime jednotlive casti na radak
        foreach ($valuesLine as $line) {
            if (!empty($line)) {
                $values[] = vsprintf($this->getOptions()->getPattern(), $line);
            } else {
                $values[] = null;
            }
        }

        $value = implode($this->getOptions()->getSeparator(), $values);

        unset($values, $valuesLine, $identifier);

        return$value;
    }
}
