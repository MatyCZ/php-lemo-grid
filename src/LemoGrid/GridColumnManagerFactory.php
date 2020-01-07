<?php

namespace LemoGrid;

use Interop\Container\ContainerInterface;
use LemoGrid\Column;
use Laminas\Mvc\Exception;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

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
