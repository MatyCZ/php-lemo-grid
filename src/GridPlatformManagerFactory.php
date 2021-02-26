<?php

namespace Lemo\Grid;

use Interop\Container\ContainerInterface;
use Laminas\Mvc\Service\AbstractPluginManagerFactory;

class GridPlatformManagerFactory extends AbstractPluginManagerFactory
{
    public function __invoke(ContainerInterface $container, $name, array $options = null): GridPlatformManager
    {
        return new GridPlatformManager(
            $container
        );
    }
}
