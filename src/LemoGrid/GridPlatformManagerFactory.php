<?php

namespace LemoGrid;

use Interop\Container\ContainerInterface;
use Zend\Mvc\Exception;
use Zend\Mvc\Service\AbstractPluginManagerFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

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
