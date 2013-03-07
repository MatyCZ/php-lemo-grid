<?php

namespace LemoGrid;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ConfigInterface;
use Zend\Stdlib\InitializableInterface;

/**
 * Plugin manager implementation for grid columns.
 *
 * Enforces that columns retrieved are instances of ColumnInterface.
 */
class GridColumnManager extends AbstractPluginManager
{
    /**
     * Default set of helpers
     *
     * @var array
     */
    protected $invokableClasses = array(
        'column'        => 'LemoGrid\Column\Column',
        'concat'        => 'LemoGrid\Column\Concat',
        'url'           => 'LemoGrid\Column\Url',
    );

    /**
     * Don't share grid columns by default
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

        $this->addInitializer(array($this, 'injectFactory'));
    }

    /**
     * Inject the factory to any column that implements GridFactoryAwareInterface
     *
     * @param $column
     */
    public function injectFactory($column)
    {
        if ($column instanceof GridFactoryAwareInterface) {
            $column->getGridFactory()->setGridColumnManager($this);
        }
    }

    /**
     * Validate the plugin
     *
     * Checks that the column is an instance of ColumnInterface
     *
     * @param  mixed $plugin
     * @throws Exception\InvalidColumnException
     * @return void
     */
    public function validatePlugin($plugin)
    {
        // Hook to perform various initialization, when the column is not created through the factory
        if ($plugin instanceof InitializableInterface) {
            $plugin->init();
        }

        if ($plugin instanceof ColumnInterface) {
            return; // we're okay
        }

        throw new Exception\InvalidColumnException(sprintf(
            'Plugin of type %s is invalid; must implement LemoGrid\ColumnInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin))
        ));
    }
}
