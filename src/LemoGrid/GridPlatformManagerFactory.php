<?php

namespace LemoGrid;

use Interop\Container\ContainerInterface;
use Laminas\Mvc\Exception;
use Laminas\Mvc\Service\AbstractPluginManagerFactory;
use Laminas\ServiceManager\ServiceLocatorInterface;

class GridPlatformManagerFactory extends AbstractPluginManagerFactory
{
    /**
     * {@inheritDoc}
     *
     * @return GridPlatformManager
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        return new GridPlatformManager($container);
    }

    /**
     * Create and return AbstractPluginManager instance
     *
     * For use with zend-servicemanager v2; proxies to __invoke().
     *
     * @param  ServiceLocatorInterface $container
     * @return GridPlatformManager
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, GridColumnManager::class);
    }
}
