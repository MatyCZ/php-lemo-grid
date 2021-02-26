<?php

namespace Lemo\Grid;

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
    protected array $invokableClasses = [
        'doctrine_entity' => Storage\Doctrine\EntityStorage::class,
        'php_session' => Storage\Php\SessionStorage::class,
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
    protected bool $shareByDefault = false;

    /**
     * Validate a plugin
     *
     * {@inheritDoc}
     */
    public function validate($plugin): void
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
}
