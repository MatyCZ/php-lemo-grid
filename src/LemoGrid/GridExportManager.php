<?php

namespace LemoGrid;

use LemoGrid\Export\ExportInterface;
use LemoGrid\Platform\PlatformInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ConfigInterface;
use Zend\Stdlib\InitializableInterface;

/**
 * Plugin manager implementation for grid exports.
 *
 * Enforces that exports retrieved are instances of ExportInterface.
 */
class GridExportManager extends AbstractPluginManager
{
    /**
     * Default set of exports
     *
     * @var array
     */
    protected $invokableClasses = array(
        'csv' => 'LemoGrid\Export\Csv',
    );

    /**
     * Don't share grid exports by default
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
     * Checks that the export is an instance of PlatformInterface
     *
     * @param  mixed $plugin
     * @throws Exception\InvalidPlatformException
     * @return void
     */
    public function validatePlugin($plugin)
    {
        // Hook to perform various initialization, when the export is not created through the factory
        if ($plugin instanceof InitializableInterface) {
            $plugin->init();
        }

        if ($plugin instanceof ExportInterface) {
            return; // we're okay
        }

        throw new Exception\InvalidPlatformException(sprintf(
            'Plugin of type %s is invalid; must implement LemoGrid\Export\ExportInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin))
        ));
    }
}
