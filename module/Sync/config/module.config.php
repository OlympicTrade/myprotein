<?php
return [
    'controllers' => [
        'invokables' => [
            'Sync\Controller\Sync' => 'Sync\Controller\SyncController',
        ],
    ],
    'router' => [
        'routes' => [
            'sync' => [
                'type'    => 'literal',
                'priority' => 400,
                'options' => [
                    'route'    => '/sync',
                    'module'     => 'Sync',
                    'section'    => 'Sync',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'stock' => [
                        'type'    => 'segment',
                        'priority' => 400,
                        'options' => [
                            'route'    => '/stock/:type/',
                            'constraints' => ['type' => '.*'],
                            'defaults' => [
                                'module'     => 'Sync',
                                'section'    => 'Sync',
                                'controller' => 'Sync\Controller\Sync',
                                'action'     => 'stock',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'sync' => __DIR__ . '/../view',
            'admin' => __DIR__ . '/../view',
        ],
    ],
];