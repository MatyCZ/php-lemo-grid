<?php

namespace LemoGrid;

use Zend\EventManager\EventInterface;
use Zend\Loader\AutoloaderFactory;
use Zend\Loader\StandardAutoloader;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;

class Module implements AutoloaderProviderInterface, BootstrapListenerInterface, ConfigProviderInterface, ServiceProviderInterface, ViewHelperProviderInterface
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
     * Listen to the bootstrap event
     *
     * @param EventInterface $e
     * @return array
     */
    public function onBootstrap(EventInterface $e)
    {
//        $serviceLocator = $e->getApplication()->getServiceManager();
//
//        $serviceListener = $serviceLocator->get('ServiceListener');
//        $serviceListener->addServiceManager(
//            'GridColumnManager',
//            'grid_columns',
//            'LemoGrid\ModuleManager\Feature\GridColumnProviderInterface',
//            'getGridColumnConfig'
//        );
//
//        $events = $serviceLocator->get('EventManager');
//        $events->attach($serviceListener);
//
//        $moduleManager = $serviceLocator->get('ModuleManager');
//        $moduleManager->attach($serviceListener);
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
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'GridColumnManager' => 'LemoGrid\Mvc\Service\GridColumnManagerFactory',
                'LemoGrid\Factory' => function($serviceManager) {
                    $factory = new Factory();
                    $factory->setGridColumnManager($serviceManager->get('GridColumnManager'));
                    return $factory;
                },
            )
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
