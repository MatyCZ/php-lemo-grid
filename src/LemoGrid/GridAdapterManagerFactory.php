<?php

namespace LemoGrid;

use Interop\Container\ContainerInterface;
use Laminas\Mvc\Exception;
use Laminas\Mvc\Service\AbstractPluginManagerFactory;
use Laminas\ServiceManager\ServiceLocatorInterface;

class GridAdapterManagerFactory extends AbstractPluginManagerFactory
{
    /**
     * {@inheritDoc}
     *
     * @return GridAdapterManager
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        return new GridAdapterManager($container);
    }

    /**
     * Create and return AbstractPluginManager instance
     *
     * For use with zend-servicemanager v2; proxies to __invoke().
     *
     * @param  ServiceLocatorInterface $container
     * @return GridAdapterManager
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, GridColumnManager::class);
    }
}
