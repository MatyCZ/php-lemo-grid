<?php

namespace Lemo\Grid;

use Interop\Container\ContainerInterface;
use Laminas\I18n\Translator\TranslatorAwareInterface;
use Laminas\Router\RouteMatch;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\Stdlib\InitializableInterface;

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
        'buttonlink'       => Column\ButtonLink::class,
        'buttonLink'       => Column\ButtonLink::class,
        'ButtonLink'       => Column\ButtonLink::class,
        'buttons'      => Column\Buttons::class,
        'Buttons'      => Column\Buttons::class,
        'concat'       => Column\Concat::class,
        'Concat'       => Column\Concat::class,
        'link'         => Column\Link::class,
        'Link'         => Column\Link::class,
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
        Column\Link::class    => Column\ColumnFactory::class,
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
     * @param ContainerInterface     $container
     * @param Column\ColumnInterface $column
     */
    public function injectRouteMatch(ContainerInterface $container, Column\ColumnInterface $column)
    {
        if (
            $column instanceof Column\Button
            || $column instanceof Column\Buttons
            || $column instanceof Column\Link
            || $column instanceof Column\Route
        ) {
            if ($container instanceof ServiceLocatorInterface && $container->has('Router')) {
                $column->setRouter($container->get('Router'));

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
     * @param ContainerInterface     $container
     * @param Column\ColumnInterface $column
     */
    public function injectTranslator(ContainerInterface $container, Column\ColumnInterface $column)
    {
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

        if ($container->has('Laminas\I18n\Translator\TranslatorInterface')) {
            $column->setTranslator($container->get('Laminas\I18n\Translator\TranslatorInterface'));
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
