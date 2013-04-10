<?php

namespace LemoGrid\Service;

use LemoGrid\GridColumnManager;
use LemoGrid\Column;
use LemoGrid\ColumnInterface;
use Zend\Console\Console;
use Zend\Mvc\Exception;
use Zend\Mvc\Router\RouteMatch;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class GridColumnManagerFactory implements FactoryInterface
{
    /**
     * Create and return the grid column manager
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @throws Exception\RuntimeException
     * @return ColumnInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $plugins = new GridColumnManager();
        $plugins->setServiceLocator($serviceLocator);

        // Configure URL view helper with router
        $plugins->setFactory('route', function ($sm) use($serviceLocator) {
            $helper = new Column\Route;
            $router = Console::isConsole() ? 'HttpRouter' : 'Router';
            $helper->setRouter($serviceLocator->get($router));

            $match = $serviceLocator->get('application')
                ->getMvcEvent()
                ->getRouteMatch();

            if ($match instanceof RouteMatch) {
                $helper->setRouteMatch($match);
            }

            return $helper;
        });

        return $plugins;
    }
}
