<?php

namespace Lemo\Grid;

use Interop\Container\ContainerInterface;
use Laminas\Mvc\Service\AbstractPluginManagerFactory;

class GridStorageManagerFactory extends AbstractPluginManagerFactory
{
    public function __invoke(ContainerInterface $container, $name, array $options = null): GridStorageManager
    {
        return new GridStorageManager(
            $container
        );
    }
}
