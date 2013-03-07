<?php

namespace LemoGrid;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ViewHelperProviderInterface
{
    /**
     * @inheritdoc
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
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
    public function getViewHelperConfig()
    {
        return array(
            'invokables' => array(
                'formRenderRow'  => 'LemoBase\Form\View\Helper\FormRenderRow',
                'formValidator'  => 'LemoBase\Form\View\Helper\FormValidator',
                'paramsQuery'    => 'LemoBase\View\Helper\ParamsQuery',
                'notice'         => 'LemoBase\View\Helper\Notice',
                'lang'           => 'LemoBase\View\Helper\Lang',
            ),
        );
    }
}
