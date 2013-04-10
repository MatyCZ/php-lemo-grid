<?php

namespace LemoGrid\Platform;

use LemoGrid\Exception;
use Traversable;

class JqGrid extends AbstractPlatform
{
    /**
     * @var JqGridOptions
     */
    protected $options;

    /**
     * Set grid options
     *
     * @param  array|Traversable|JqGridOptions $options
     * @throws Exception\InvalidArgumentException
     * @return JqGrid
     */
    public function setOptions($options)
    {
        if (!$options instanceof JqGridOptions) {
            if (is_object($options) && !$options instanceof Traversable) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Expected instance of LemoGrid\Platform\JqGridOptions; '
                        . 'received "%s"', get_class($options))
                );
            }

            $options = new JqGridOptions($options);
        }

        $this->options = $options;

        return $this;
    }

    /**
     * Get grid options
     *
     * @return JqGridOptions
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new JqGridOptions());
        }

        return $this->options;
    }

    /**
     * Is the grid rendered?
     *
     * @return bool
     */
    public function isRendered()
    {
        if (null === $this->getGrid()->getParam('_name')) {
            return false;
        }

        return true;
    }

    /**
     * Return sort by column index
     *
     * @return string
     */
    public function getSortColumn()
    {
        if ($this->getGrid()->hasParam('sidx')) {
            return $this->getGrid()->getParam('sidx');
        } else {
            return $this->getOptions()->getSortName();
        }
    }

    /**
     * Return sort direct
     *
     * @throws Exception\UnexpectedValueException
     * @return string
     */
    public function getSortDirect()
    {
        if ($this->getGrid()->hasParam('sord')) {
            if(strtolower($this->getGrid()->getParam('sord')) != 'asc' && strtolower($this->getGrid()->getParam('sord')) != 'desc') {
                throw new Exception\UnexpectedValueException('Sort direct must be ' . 'asc' . ' or ' . 'desc' . '!');
            }

            return $this->getGrid()->getParam('sord');
        } else {
            return $this->getOptions()->getSortOrder();
        }
    }
}
