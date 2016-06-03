<?php

namespace LemoGrid;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\InvalidServiceException;
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
    protected $aliases = [
        'jqgrid' => Platform\JqGridPlatform::class,
    ];

    /**
     * Don't share form elements by default (v3)
     *
     * @var bool
     */
    protected $sharedByDefault = false;

    /**
     * Don't share form elements by default (v2)
     *
     * @var bool
     */
    protected $shareByDefault = false;

    /**
     * Plugins must be of this type.
     *
     * @var string
     */
    protected $instanceOf = Platform\PlatformInterface::class;

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
     * @throws Exception\InvalidPlatformException
     */
    public function validatePlugin($plugin)
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new Exception\InvalidPlatformException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
