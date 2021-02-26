<?php

namespace Lemo\Grid;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class GridColumnManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $name, array $options = null): GridColumnManager
    {
        return new GridColumnManager(
            $container
        );
    }
}
