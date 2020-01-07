<?php

namespace LemoGrid;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

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
     * @var GridFactory
     */
    protected $factory;

    /**
     * Create a form (v3)
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return GridInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config  = $this->getConfig($container);
        $config  = $config[$requestedName];
        $factory = $container->get('GridFactory');

        return $factory->createGrid($config);
    }

    /**
     * Can we create an instance of the given service? (v3 usage).
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        // avoid infinite loops when looking up config
        if ($requestedName == 'config') {
            return false;
        }

        $config = $this->getConfig($container);

        if (empty($config)) {
            return false;
        }

        $containerName = $this->normalizeContainerName($requestedName);
        return array_key_exists($containerName, $config);
    }

    /**
     * Can we create the requested service? (v2)
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param string $name
     * @param string $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return $this->canCreate($serviceLocator, $requestedName);
    }

    /**
     * Create and return a named container (v2 usage).
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @param  string                  $name
     * @param  string                  $requestedName
     * @return GridInterface
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return $this($serviceLocator, $requestedName);
    }

    /**
     * Retrieve config from service locator, and cache for later
     *
     * @param  ContainerInterface $container
     * @return false|array
     */
    protected function getConfig(ContainerInterface $container)
    {
        if (null !== $this->config) {
            return $this->config;
        }

        if (!$container->has('config')) {
            $this->config = [];
            return $this->config;
        }

        $config = $container->get('config');
        if (! isset($config[$this->configKey]) || ! is_array($config[$this->configKey])) {
            $this->config = [];
            return $this->config;
        }

        $this->config = $config[$this->configKey];
        return $this->config;
    }

    /**
     * Normalize the container name in order to perform a lookup
     *
     * @param  string $name
     * @return string
     */
    protected function normalizeContainerName($name)
    {
        return strtolower($name);
    }
}
