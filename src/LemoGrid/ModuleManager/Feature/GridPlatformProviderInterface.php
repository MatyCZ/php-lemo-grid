<?php

namespace LemoGrid\ModuleManager\Feature;

interface GridPlatformProviderInterface
{
    /**
     * Expected to return \Zend\ServiceManager\Config object or array to
     * seed such an object.
     *
     * @return array|\Zend\ServiceManager\Config
     */
    public function getGridPlatformConfig();
}