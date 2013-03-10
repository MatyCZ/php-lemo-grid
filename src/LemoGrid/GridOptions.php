<?php

namespace LemoGrid;

use Zend\Stdlib\AbstractOptions;

class GridOptions extends AbstractOptions
{
    const NAMESPACE_DEFAULT = 'LemoGrid';

    /**
     * @var string
     */
    protected $sessionNamespace = self::NAMESPACE_DEFAULT;

    /**
     * @param string $sessionNamespace
     * @return GridOptions
     */
    public function setSessionNamespace($sessionNamespace)
    {
        $this->sessionNamespace = $sessionNamespace;

        return $this;
    }

    /**
     * @return string
     */
    public function getSessionNamespace()
    {
        return $this->sessionNamespace;
    }
}
