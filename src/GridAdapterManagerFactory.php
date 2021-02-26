<?php

namespace Lemo\Grid;

use Interop\Container\ContainerInterface;
use Laminas\Mvc\Service\AbstractPluginManagerFactory;

class GridAdapterManagerFactory extends AbstractPluginManagerFactory
{
    public function __invoke(ContainerInterface $container, $name, array $options = null): GridAdapterManager
    {
        return new GridAdapterManager(
            $container
        );
    }
}
