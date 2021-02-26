<?php

namespace Lemo\Grid;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\Stdlib\InitializableInterface;

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
    protected $aliases = [
        'jqgrid' => Platform\JqGridPlatform::class,
    ];

    /**
     * Don't share form elements by default
     *
     * @var bool
     */
    protected $sharedByDefault = false;

    /**
     * Plugins must be of this type.
     *
     * @var string
     */
    protected $instanceOf = Platform\PlatformInterface::class;

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
