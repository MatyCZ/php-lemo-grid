<?php

namespace LemoGrid;

use LemoGrid\Column\ColumnInterface;
use Zend\Console\Console;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\Mvc\Router\RouteMatch;
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
     * Default set of columns
     *
     * @var array
     */
    protected $invokableClasses = array(
        'concat' => 'LemoGrid\Column\Concat',
        'route'  => 'LemoGrid\Column\Route',
        'text'   => 'LemoGrid\Column\Text',
    );

    /**
     * @var bool
     */
    protected $shareByDefault = false;

    /**
     * @param ConfigInterface $configuration
     */
    public function __construct(ConfigInterface $configuration = null)
    {
        parent::__construct($configuration);

        $this->addInitializer(array($this, 'injectRouter'));
        $this->addInitializer(array($this, 'injectTranslator'));
    }

    /**
     * Inject translator to any column that implements TranslatorAwareInterface
     *
     * @param  ColumnInterface $column
     * @return void
     */
    public function injectRouter($column)
    {
        if ($column instanceof Column\Route) {
            $locator = $this->getServiceLocator();
            $router = Console::isConsole() ? 'HttpRouter' : 'Router';

            if ($locator && $locator->has($router)) {
                $column->setRouter($locator->get($router));

                $match = $locator->get('application')
                    ->getMvcEvent()
                    ->getRouteMatch();

                if ($match instanceof RouteMatch) {
                    $column->setRouteMatch($match);
                }
            }
        }
    }

    /**
     * Inject translator to any column that implements TranslatorAwareInterface
     *
     * @param  ColumnInterface $column
     * @return void
     */
    public function injectTranslator($column)
    {
        if ($column instanceof TranslatorAwareInterface) {
            $locator = $this->getServiceLocator();

            if ($locator && $locator->has('translator')) {
                $column->setTranslator($locator->get('translator'));
            }
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
