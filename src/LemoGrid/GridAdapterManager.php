<?php

namespace LemoGrid;

use LemoGrid\Adapter\AdapterInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ConfigInterface;
use Zend\Stdlib\InitializableInterface;

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
    protected $invokableClasses = array(
        'doctrine_querybuilder' => 'LemoGrid\Adapter\Doctrine\QueryBuilder',
        'zend_db_sql'           => 'LemoGrid\Adapter\Zend\Db\Sql',
    );

    /**
     * Don't share grid adapters by default
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
            'Plugin of type %s is invalid; must implement LemoGrid\AdapterInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin))
        ));
    }
}
