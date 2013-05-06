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

        $serviceLocator = $event->getParam('ServiceManager');
        $serviceListener = $serviceLocator->get('ServiceListener');

        $eventManager->detach($serviceListener);

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
            'aliases' => array(
                'GridColumnManager'   => 'LemoGrid\Mvc\Service\GridColumnManagerFactory',
            ),
            'factories' => array(
                'GridAdapterManager'  => 'LemoGrid\Mvc\Service\GridAdapterManagerFactory',
                'GridPlatformManager' => 'LemoGrid\Mvc\Service\GridPlatformManagerFactory',
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
                    return $instance;
                },
            ),
            'initializers' => array(
                function ($instance, $sm) {
                    if ($instance instanceof GridFactoryAwareInterface) {
                        $factory = $sm->get('LemoGrid\Factory');
                        $instance->setGridFactory($factory);
                    }
                }
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
