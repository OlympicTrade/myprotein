<?php
return [
    'controllers' => [
        'invokables' => [
            'Sync\Controller\Sync' => 'Sync\Controller\SyncController',
            'Sync\Controller\Vkontakte' => 'Sync\Controller\VkontakteController',
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
                    'messengers' => [
                        'type'    => 'segment',
                        'priority' => 400,
                        'options' => [
                            'route'    => '/vkontakte[/:action]/',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ],
                            'defaults' => [
                                'module'     => 'Sync',
                                'section'    => 'Sync',
                                'controller' => 'Sync\Controller\Vkontakte',
                                'action'     => 'index',
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