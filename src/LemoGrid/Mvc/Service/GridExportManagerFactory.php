<?php

namespace LemoGrid\Mvc\Service;

use LemoGrid\Export\ExportInterface;
use Zend\Mvc\Exception;
use Zend\Mvc\Service\AbstractPluginManagerFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class GridExportManagerFactory extends AbstractPluginManagerFactory
{
    const PLUGIN_MANAGER_CLASS = 'LemoGrid\GridExportManager';

    /**
     * Create and return the view helper manager
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return ExportInterface
     * @throws Exception\RuntimeException
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $plugins = parent::createService($serviceLocator);
        return $plugins;
    }
}
