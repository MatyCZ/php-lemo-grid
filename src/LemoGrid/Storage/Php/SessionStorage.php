<?php

namespace LemoGrid\Storage\Php;

use ArrayIterator;
use LemoGrid\Storage\StorageInterface;
use Zend\Session\Container as SessionContainer;
use Zend\Session\ManagerInterface as SessionManager;

class SessionStorage implements StorageInterface
{
    /**
     * Default session namespace
     */
    const NAMESPACE_DEFAULT = 'Lemo_Grid';

    /**
     * Session namespace
     *
     * @var mixed
     */
    protected $namespace = self::NAMESPACE_DEFAULT;

    /**
     * Object to proxy $_SESSION storage
     *
     * @var SessionContainer
     */
    protected $session;

    /**
     * Sets session storage options and initializes session namespace object
     *
     * @param  mixed          $namespace
     * @param  SessionManager $manager
     */
    public function __construct($namespace = null, SessionManager $manager = null)
    {
        if ($namespace !== null) {
            $this->namespace = $namespace;
        }

        $this->session = new SessionContainer($this->namespace, $manager);
    }

    /**
     * Defined by Zend\Authentication\Storage\StorageInterface
     *
     * @param  string $key
     * @return bool
     */
    public function isEmpty($key)
    {
        return !isset($this->session->{$key});
    }

    /**
     * Defined by Zend\Authentication\Storage\StorageInterface
     *
     * @param  string $key
     * @return ArrayIterator
     */
    public function read($key)
    {
        return $this->session->{$key};
    }

    /**
     * Defined by Zend\Authentication\Storage\StorageInterface
     *
     * @param  string $key
     * @param  mixed  $content
     * @return SessionStorage
     */
    public function write($key, $content)
    {
        $this->session->{$key} = $content;

        return $this;
    }

    /**
     * Defined by Zend\Authentication\Storage\StorageInterface
     *
     * @param  string $key
     * @return SessionStorage
     */
    public function clear($key)
    {
        unset($this->session->{$key});

        return $this;
    }
}
