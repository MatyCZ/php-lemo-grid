<?php

namespace Lemo\Grid;

return [
    'service_manager' => [
        'abstract_factories' => [
            GridAbstractServiceFactory::class,
        ],
        'aliases' => [
            'GridAdapterManager' => GridAdapterManager::class,
            'GridColumnManager' => GridAdapterManager::class,
            'GridFactory' => GridFactory::class,
            'GridPlatformManager' => GridPlatformManager::class,
            'GridStorageManager' => GridStorageManager::class,
        ],
        'factories' => [
            GridAdapterManager::class => GridAdapterManagerFactory::class,
            GridColumnManager::class => GridColumnManagerFactory::class,
            GridPlatformManager::class => GridPlatformManagerFactory::class,
            GridStorageManager::class => GridStorageManagerFactory::class,
            GridFactory::class => GridFactoryFactory::class,
        ],
    ],
    'view_helpers' => [
        'aliases' => [
            'jqgrid' => View\Helper\JqGrid::class,
        ],
        'invokables' => [
            View\Helper\JqGrid::class => View\Helper\JqGrid::class,
        ],
    ]
];
