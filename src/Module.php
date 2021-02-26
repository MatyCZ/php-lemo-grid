<?php

namespace Lemo\Grid;

use Laminas\ModuleManager\Listener\ServiceListener;
use Laminas\ModuleManager\ModuleManagerInterface;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\Feature\InitProviderInterface;
use Laminas\ServiceManager\ServiceManager;

class Module implements
    ConfigProviderInterface,
    InitProviderInterface
{
    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * @inheritdoc
     */
    public function init(ModuleManagerInterface $manager)
    {
        $event = $manager->getEvent();

        /** @var ServiceManager $serviceManager */
        $serviceManager = clone $event->getParam('ServiceManager');

        /** @var ServiceListener $serviceListener */
        $serviceListener = $serviceManager->get('ServiceListener');

        // Add managers to listener
        $serviceListener->addServiceManager(
            ServiceManager::class,
            'grids',
            ModuleManager\Feature\GridProviderInterface::class,
            'getGridConfig'
        );
        $serviceListener->addServiceManager(
            GridAdapterManager::class,
            'grid_adapters',
            ModuleManager\Feature\GridAdapterProviderInterface::class,
            'getGridAdapterConfig'
        );
        $serviceListener->addServiceManager(
            GridColumnManager::class,
            'grid_columns',
            ModuleManager\Feature\GridColumnProviderInterface::class,
            'getGridColumnConfig'
        );
        $serviceListener->addServiceManager(
            GridPlatformManager::class,
            'grid_platforms',
            ModuleManager\Feature\GridPlatformProviderInterface::class,
            'getGridPlatformConfig'
        );
        $serviceListener->addServiceManager(
            GridStorageManager::class,
            'grid_storages',
            ModuleManager\Feature\GridStorageProviderInterface::class,
            'getGridStorageConfig'
        );

        // Add initializer to service manager
        $serviceManager->addInitializer(function ($instance) use ($serviceManager) {
            if ($instance instanceof GridFactoryAwareInterface) {
                $instance->setGridFactory($serviceManager->get(GridFactory::class));
            }
        });
    }
}
