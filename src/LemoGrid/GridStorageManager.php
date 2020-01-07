<?php

namespace LemoGrid;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\Stdlib\InitializableInterface;

/**
 * Plugin manager implementation for grid storages.
 *
 * Enforces that storages retrieved are instances of StorageInterface.
 */
class GridStorageManager extends AbstractPluginManager
{
    /**
     * Default set of storages
     *
     * @var array
     */
    protected $invokableClasses = [
        'doctrine_entity' => Storage\Doctrine\EntityStorage::class,
        'php_session    ' => Storage\Php\SessionStorage::class,
    ];

    /**
     * Plugins must be of this type.
     *
     * @var string
     */
    protected $instanceOf = Storage\StorageInterface::class;

    /**
     * Don't share grid storages by default
     *
     * @var bool
     */
    protected $shareByDefault = false;

    /**
     * Validate a plugin (v3)
     *
     * {@inheritDoc}
     */
    public function validate($plugin)
    {
        if (! $plugin instanceof $this->instanceOf) {
            throw new InvalidServiceException(sprintf(
                'Column of type "%s" is invalid; must implement %s',
                (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
                $this->instanceOf
            ));
        }


        // Hook to perform various initialization, when the column is not created through the factory
        if ($plugin instanceof InitializableInterface) {
            $plugin->init();
        }
    }

    /**
     * Validate a plugin (v2)
     *
     * {@inheritDoc}
     *
     * @throws Exception\InvalidStorageException
     */
    public function validatePlugin($plugin)
    {
        try {
            $this->validate($plugin);
        } catch (Exception\InvalidStorageException $e) {
            throw new Exception\InvalidPlatformException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
