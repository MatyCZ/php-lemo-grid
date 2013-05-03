<?php

namespace LemoGrid;

use Zend\Loader\AutoloaderFactory;
use Zend\Loader\StandardAutoloader;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ServiceProviderInterface, ViewHelperProviderInterface
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
                'LemoGrid\Factory' => function ($sm) {
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
