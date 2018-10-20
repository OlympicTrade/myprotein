<?php
return [
    'controllers' => [
        'invokables' => [
            'Callcenter\Controller\Callcenter'      => 'Callcenter\Controller\CallcenterController',
            'CallcenterAdmin\Controller\Callcenter' => 'CallcenterAdmin\Controller\CallcenterController',
        ],
    ],
    'router' => [
        'routes' => [
            'callcenter' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '[/:locale]/callcenter[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z0-9_-]+',
                    ],
                    'defaults' => [
                        'module'     => 'Callcenter',
                        'section'    => 'Callcenter',
                        'controller' => 'Callcenter\Controller\Callcenter',
                        'action'     => 'index',
                    ],
                ],
            ],
            'adminCallcenter' => [
                'type'    => 'segment',
                'priority' => 600,
                'options' => [
                    'route'    => '/admin/callcenter/callcenter[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'module'     => 'Callcenter',
                        'section'    => 'Callcenter',
                        'controller' => 'CallcenterAdmin\Controller\Callcenter',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'callcenter' => __DIR__ . '/../view',
            'admin' => __DIR__ . '/../view',
        ],
    ],
];