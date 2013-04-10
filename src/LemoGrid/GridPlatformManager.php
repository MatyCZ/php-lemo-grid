<?php

namespace LemoGrid;

use LemoGrid\Platform\PlatformInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ConfigInterface;
use Zend\Stdlib\InitializableInterface;

/**
 * Plugin manager implementation for grid platforms.
 *
 * Enforces that platforms retrieved are instances of PlatformInterface.
 */
class GridPlatformManager extends AbstractPluginManager
{
    /**
     * Default set of platforms
     *
     * @var array
     */
    protected $invokableClasses = array(
        'jqgrid' => 'LemoGrid\Platform\JqGrid',
    );

    /**
     * Don't share grid platforms by default
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
     * Checks that the platform is an instance of PlatformInterface
     *
     * @param  mixed $plugin
     * @throws Exception\InvalidPlatformException
     * @return void
     */
    public function validatePlugin($plugin)
    {
        // Hook to perform various initialization, when the platform is not created through the factory
        if ($plugin instanceof InitializableInterface) {
            $plugin->init();
        }

        if ($plugin instanceof PlatformInterface) {
            return; // we're okay
        }

        throw new Exception\InvalidPlatformException(sprintf(
            'Plugin of type %s is invalid; must implement LemoGrid\PlatformInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin))
        ));
    }
}
