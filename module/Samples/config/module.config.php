<?php
return [
    'controllers' => [
        'invokables' => [
            'Samples\Controller\Samples'      => 'Samples\Controller\SamplesController',
            'SamplesAdmin\Controller\Samples' => 'SamplesAdmin\Controller\SamplesController',
        ],
    ],
    'router' => [
        'routes' => [
            'samples' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '[/:locale]/samples[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z0-9_-]+',
                    ],
                    'defaults' => [
                        'module'     => 'Samples',
                        'section'    => 'Samples',
                        'controller' => 'Samples\Controller\Samples',
                        'action'     => 'index',
                    ],
                ],
            ],
            'adminSamples' => [
                'type'    => 'segment',
                'priority' => 600,
                'options' => [
                    'route'    => '/admin/samples/samples[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'module'     => 'Samples',
                        'section'    => 'Samples',
                        'controller' => 'SamplesAdmin\Controller\Samples',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'samples' => __DIR__ . '/../view',
            'admin' => __DIR__ . '/../view',
        ],
    ],
];