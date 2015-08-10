<?php

namespace LemoGrid;

use LemoGrid\Storage\StorageInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ConfigInterface;
use Zend\Stdlib\InitializableInterface;

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
    protected $invokableClasses = array(
        'doctrine_entity' => 'LemoGrid\Storage\Doctrine\EntityStorage',
        'php_session    ' => 'LemoGrid\Storage\Php\SessionStorage',
    );

    /**
     * Don't share grid storages by default
     *
     * @var bool
     */
    protected $shareByDefault = false;

    /**
     * @param ConfigInterface $configuration
     */
    public function __construct(ConfigInterface $configuration = null)
    {
        parent::__construct($configuration);
    }

    /**
     * Validate the plugin
     *
     * Checks that the storage is an instance of StorageInterface
     *
     * @param  mixed $plugin
     * @throws Exception\InvalidStorageException
     * @return void
     */
    public function validatePlugin($plugin)
    {
        // Hook to perform various initialization, when the storage is not created through the factory
        if ($plugin instanceof InitializableInterface) {
            $plugin->init();
        }

        if ($plugin instanceof StorageInterface) {
            return; // we're okay
        }

        throw new Exception\InvalidStorageException(sprintf(
            'Storage of type %s is invalid; must implement LemoGrid\Storage\StorageInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin))
        ));
    }
}
