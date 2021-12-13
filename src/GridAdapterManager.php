<?php

namespace Lemo\Grid;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\Stdlib\InitializableInterface;
use Lemo\Grid\Adapter\AdapterInterface;

/**
 * Plugin manager implementation for grid adapters.
 *
 * Enforces that adapters retrieved are instances of AdapterInterface.
 */
class GridAdapterManager extends AbstractPluginManager
{
    /**
     * Default set of adapters
     *
     * @var array
     */
    protected $invokableClasses = [
        'doctrine_querybuilder' => 'Lemo\Grid\Adapter\Doctrine\QueryBuilderAdapter',
        'php_array'             => 'Lemo\Grid\Adapter\Php\ArrayAdapter',
    ];

    /**
     * Don't share grid adapters by default
     *
     * @var bool
     */
    protected $shareByDefault = false;

    /**
     * Validate the plugin
     *
     * Checks that the adapter is an instance of AdapterInterface
     *
     * @param  mixed $plugin
     * @throws Exception\InvalidAdapterException
     * @return void
     */
    public function validatePlugin($plugin)
    {
        // Hook to perform various initialization, when the adapter is not created through the factory
        if ($plugin instanceof InitializableInterface) {
            $plugin->init();
        }

        if ($plugin instanceof AdapterInterface) {
            return; // we're okay
        }

        throw new Exception\InvalidAdapterException(sprintf(
            'Adapter of type %s is invalid; must implement Lemo\Grid\Adapter\AdapterInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin))
        ));
    }
}
