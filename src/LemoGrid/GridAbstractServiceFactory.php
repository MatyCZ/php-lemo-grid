<?php

namespace LemoGrid;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class GridAbstractServiceFactory implements AbstractFactoryInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Top-level configuration key indicating grids configuration
     *
     * @var string
     */
    protected $configKey  = 'grids';

    /**
     * Grid factory used to create grids
     *
     * @var Factory
     */
    protected $factory;

    /**
     * Can we create the requested service?
     *
     * @param  ServiceLocatorInterface $services
     * @param  string $name Service name (as resolved by ServiceManager)
     * @param  string $rName Name by which service was requested
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $services, $name, $rName)
    {
        $config = $this->getConfig($services);
        if (empty($config)) {
            return false;
        }

        return (isset($config[$rName]) && is_array($config[$rName]) && !empty($config[$rName]));
    }

    /**
     * Create a grid
     *
     * @param  ServiceLocatorInterface $services
     * @param  string $name Service name (as resolved by ServiceManager)
     * @param  string $rName Name by which service was requested
     * @return Grid
     */
    public function createServiceWithName(ServiceLocatorInterface $services, $name, $rName)
    {
        $config  = $this->getConfig($services);
        $config  = $config[$rName];
        $factory = $this->getGridFactory($services);

        return $factory->createGrid($config);
    }

    /**
     * Get grids configuration, if any
     *
     * @param  ServiceLocatorInterface $services
     * @return array
     */
    protected function getConfig(ServiceLocatorInterface $services)
    {
        if ($this->config !== null) {
            return $this->config;
        }

        if (!$services->has('Config')) {
            $this->config = array();
            return $this->config;
        }

        $config = $services->get('Config');
        if (!isset($config[$this->configKey])
            || !is_array($config[$this->configKey])
        ) {
            $this->config = array();
            return $this->config;
        }

        $this->config = $config[$this->configKey];
        return $this->config;
    }

    /**
     * Retrieve the grid factory, creating it if necessary
     *
     * @param  ServiceLocatorInterface $services
     * @return Factory
     */
    protected function getGridFactory(ServiceLocatorInterface $services)
    {
        if ($this->factory instanceof Factory) {
            return $this->factory;
        }

        $adapterManager = null;
        if ($services->has('GridAdapterManager')) {
            $adapterManager = $services->get('GridAdapterManager');
        }

        $columnManager = null;
        if ($services->has('GridColumnManager')) {
            $columnManager = $services->get('GridColumnManager');
        }

        $platformManager = null;
        if ($services->has('GridPlatformManager')) {
            $platformManager = $services->get('GridPlatformManager');
        }

        $this->factory = new Factory($platformManager, $adapterManager, $columnManager);
        return $this->factory;
    }
}
