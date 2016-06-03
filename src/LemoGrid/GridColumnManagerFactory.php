<?php

namespace LemoGrid;

use Interop\Container\ContainerInterface;
use LemoGrid\Column;
use Zend\Mvc\Exception;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class GridColumnManagerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return GridColumnManager
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        return new GridColumnManager($container);
    }

    /**
     * Create and return AbstractPluginManager instance
     *
     * For use with zend-servicemanager v2; proxies to __invoke().
     *
     * @param  ServiceLocatorInterface $container
     * @return GridColumnManager
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, GridColumnManager::class);
    }
}
