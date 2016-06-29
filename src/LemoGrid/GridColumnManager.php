<?php

namespace LemoGrid;

use Interop\Container\ContainerInterface;
use Zend\Console\Console;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\Router\RouteMatch;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\Stdlib\InitializableInterface;

/**
 * Plugin manager implementation for grid columns.
 *
 * Enforces that columns retrieved are instances of ColumnInterface.
 */
class GridColumnManager extends AbstractPluginManager
{
    /**
     * Aliases for default set of helpers
     *
     * @var array
     */
    protected $aliases = [
        'button'       => Column\Button::class,
        'Button'       => Column\Button::class,
        'buttons'      => Column\Buttons::class,
        'Buttons'      => Column\Buttons::class,
        'concat'       => Column\Concat::class,
        'Concat'       => Column\Concat::class,
        'number'       => Column\Number::class,
        'Number'       => Column\Number::class,
        'route'        => Column\Route::class,
        'Route'        => Column\Route::class,
        'text'         => Column\Text::class,
        'Text'         => Column\Text::class,
    ];
    /**
     * Aliases for default set of helpers
     *
     * @var array
     */
    protected $factories = [
        Column\Button::class  => Column\ColumnFactory::class,
        Column\Buttons::class => Column\ColumnFactory::class,
        Column\Concat::class  => Column\ColumnFactory::class,
        Column\Number::class  => Column\ColumnFactory::class,
        Column\Route::class   => Column\ColumnFactory::class,
        Column\Text::class    => Column\ColumnFactory::class,
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
    protected $instanceOf = Column\ColumnInterface::class;

    /**
     * @inheritdoc
     */
    public function __construct($configOrContainerInstance = null, array $v3config = [])
    {
        $this->initializers[] = [$this, 'injectRouteMatch'];
        $this->initializers[] = [$this, 'injectTranslator'];

        parent::__construct($configOrContainerInstance, $v3config);
    }

    /**
     * Inject a helper instance with the registered translator
     *
     * @param ContainerInterface|Column\ColumnInterface $first helper instance
     *     under zend-servicemanager v2, ContainerInterface under v3.
     * @param ContainerInterface|Column\ColumnInterface $second
     *     ContainerInterface under zend-servicemanager v3, helper instance
     *     under v2. Ignored regardless.
     */
    public function injectRouteMatch($first, $second)
    {
        if ($first instanceof ContainerInterface) {
            // v3 usage
            $container = $first;
            $column = $second;
        } else {
            // v2 usage; grab the parent container
            $container = $second->getServiceLocator();
            $column = $first;
        }

        if ($column instanceof Column\Route || $column instanceof Column\Button || $column instanceof Column\Buttons) {
            $router = Console::isConsole() ? 'HttpRouter' : 'Router';

            if ($container instanceof ServiceLocatorInterface && $container->has($router)) {
                $column->setRouter($container->get($router));

                $match = $container->get('application')
                    ->getMvcEvent()
                    ->getRouteMatch();

                if ($match instanceof RouteMatch) {
                    $column->setRouteMatch($match);
                }
            }
        }
    }

    /**
     * Inject a helper instance with the registered translator
     *
     * @param ContainerInterface|Column\ColumnInterface $first helper instance
     *     under zend-servicemanager v2, ContainerInterface under v3.
     * @param ContainerInterface|Column\ColumnInterface $second
     *     ContainerInterface under zend-servicemanager v3, helper instance
     *     under v2. Ignored regardless.
     */
    public function injectTranslator($first, $second)
    {
        if ($first instanceof ContainerInterface) {
            // v3 usage
            $container = $first;
            $column = $second;
        } else {
            // v2 usage; grab the parent container
            $container = $second->getServiceLocator();
            $column = $first;
        }

        if (!$column instanceof TranslatorAwareInterface) {
            return;
        }

        if (!$container) {
            // Under zend-navigation v2.5, the navigation PluginManager is
            // always lazy-loaded, which means it never has a parent
            // container.
            return;
        }

        if ($container->has('MvcTranslator')) {
            $column->setTranslator($container->get('MvcTranslator'));
            return;
        }

        if ($container->has('Zend\I18n\Translator\TranslatorInterface')) {
            $column->setTranslator($container->get('Zend\I18n\Translator\TranslatorInterface'));
            return;
        }

        if ($container->has('Translator')) {
            $column->setTranslator($container->get('Translator'));
            return;
        }
    }

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
     * @throws Exception\InvalidColumnException
     */
    public function validatePlugin($plugin)
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new Exception\InvalidColumnException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
