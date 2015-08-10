<?php

namespace LemoGrid;

use Zend\Loader\AutoloaderFactory;
use Zend\Loader\StandardAutoloader;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\InitProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

class Module implements
    AutoloaderProviderInterface,
    ConfigProviderInterface,
    InitProviderInterface,
    ServiceProviderInterface,
    ViewHelperProviderInterface
{
    /**
     * @inheritdoc
     */
    public function getAutoloaderConfig()
    {
        return array(
            AutoloaderFactory::STANDARD_AUTOLOADER => array(
                StandardAutoloader::LOAD_NS => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * @inheritdoc
     */
    public function init(ModuleManagerInterface $moduleManager)
    {
        $event = $moduleManager->getEvent();
        $eventManager = $moduleManager->getEventManager();

        $serviceManager = $event->getParam('ServiceManager');
        $serviceListener = $serviceManager->get('ServiceListener');

        $eventManager->detach($serviceListener);

        // Add managers to listener
        $serviceListener->addServiceManager(
            'ServiceManager',
            'grids',
            'LemoGrid\ModuleManager\Feature\GridProviderInterface',
            'getGridConfig'
        );
        $serviceListener->addServiceManager(
            'GridAdapterManager',
            'grid_adapters',
            'LemoGrid\ModuleManager\Feature\GridAdapterProviderInterface',
            'getGridAdapterConfig'
        );
        $serviceListener->addServiceManager(
            'GridColumnManager',
            'grid_columns',
            'LemoGrid\ModuleManager\Feature\GridColumnProviderInterface',
            'getGridColumnConfig'
        );
        $serviceListener->addServiceManager(
            'GridPlatformManager',
            'grid_platforms',
            'LemoGrid\ModuleManager\Feature\GridPlatformProviderInterface',
            'getGridPlatformConfig'
        );
        $serviceListener->addServiceManager(
            'GridStorageManager',
            'grid_storages',
            'LemoGrid\ModuleManager\Feature\GridStorageProviderInterface',
            'getGridStorageConfig'
        );

        // Add initializer to service manager
        $serviceManager->addInitializer(function ($instance) use ($serviceManager) {
            if ($instance instanceof GridFactoryAwareInterface) {
                $instance->setGridFactory($serviceManager->get('LemoGrid\Factory'));
            }
        });

        $eventManager->attach($serviceListener);
    }

    /**
     * @inheritdoc
     */
    public function getServiceConfig()
    {
        return array(
            'abstract_factories' => array(
                'LemoGrid\GridAbstractServiceFactory',
            ),
            'factories' => array(
                'GridAdapterManager'  => 'LemoGrid\Mvc\Service\GridAdapterManagerFactory',
                'GridColumnManager'   => 'LemoGrid\Mvc\Service\GridColumnManagerFactory',
                'GridPlatformManager' => 'LemoGrid\Mvc\Service\GridPlatformManagerFactory',
                'GridStorageManager'  => 'LemoGrid\Mvc\Service\GridStorageManagerFactory',
                'LemoGrid\Mvc\Service\GridColumnManagerFactory' => function ($sm) {
                    $instance = new GridColumnManager();
                    $instance->setServiceLocator($sm);
                    return $instance;
                },
                'LemoGrid\Factory'    => function ($sm) {
                    $instance = new Factory();
                    $instance->setGridAdapterManager($sm->get('GridAdapterManager'));
                    $instance->setGridColumnManager($sm->get('GridColumnManager'));
                    $instance->setGridPlatformManager($sm->get('GridPlatformManager'));
                    $instance->setGridStorageManager($sm->get('GridStorageManager'));
                    return $instance;
                },
            ),
        );
    }

    /**
     * @inheritdoc
     */
    public function getViewHelperConfig()
    {
        return array(
            'invokables' => array(
                'jqgrid' => 'LemoGrid\View\Helper\JqGrid',
            ),
        );
    }
}
