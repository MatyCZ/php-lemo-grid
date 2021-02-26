<?php

namespace Lemo\Grid;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class GridFactoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $name, array $options = null): GridFactory
    {
        return new GridFactory(
            $container->get(GridAdapterManager::class),
            $container->get(GridColumnManager::class),
            $container->get(GridPlatformManager::class),
            $container->get(GridStorageManager::class)
        );
    }
}
