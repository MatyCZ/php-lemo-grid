<?php

namespace LemoGrid\Column;

use DateTime;
use LemoGrid\Adapter\AdapterInterface;
use LemoGrid\Exception;
use Traversable;

class Concat extends AbstractColumn
{
    /**
     * Column options
     *
     * @var ConcatOptions
     */
    protected $options;

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

    /**
     * @param  AdapterInterface $adapter
     * @param  array            $item
     * @return string
     */
    public function renderValue(AdapterInterface $adapter, array $item)
    {
        $value = null;
        $values = array();
        $hasValue = false;

        foreach($this->getOptions()->getIdentifiers() as $index => $identifier) {
            $val = $adapter->findValue($identifier, $item);

            if(!empty($val)) {
                if($val instanceof DateTime) {
                    $val = $value->format('Y-m-d H:i:s');
                }

                $values[$index] = $val;

                if ('' !== $val) {
                    $hasValue = true;
                }
            } else {
                $values[$index] = '';
            }
        }

        $patternCount = count($values);
        $patternCountParts = substr_count($this->getOptions()->getPattern(), '%s');
        if (true === $hasValue && $patternCount > 0 && $patternCount == $patternCountParts) {
            $value = vsprintf($this->getOptions()->getPattern(), $values);
        }

        unset($values, $identifier);

        return $value;
    }
}
