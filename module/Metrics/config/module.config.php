<?php
return [
    'controllers' => [
        'invokables' => [
            'MetricsAdmin\Controller\Adwords' => 'MetricsAdmin\Controller\AdwordsController',
            'MetricsAdmin\Controller\Metrics' => 'MetricsAdmin\Controller\MetricsController',
            'MetricsAdmin\Controller\Cash'    => 'MetricsAdmin\Controller\CashController',
            'Metrics\Controller\Metrics'      => 'Metrics\Controller\MetricsController',
        ],
    ],
    'router' => [
        'routes' => [
            'metrics' => [
                'type'    => 'literal',
                'priority' => 600,
                'options' => [
                    'route'    => '/metrics/init/',
                    'defaults' => [
                        'module'     => 'Metrics',
                        'section'    => 'Metrics',
                        'controller' => 'Metrics\Controller\Metrics',
                        'action'     => 'init',
                    ],
                ],
            ],
			'adminMetrics' => [
                'type'    => 'literal',
                'priority' => 600,
                'options' => [
                    'route'    => '/admin',
                ],
				'may_terminate' => true,
                'child_routes' => [
					'adwords' => [
						'type'    => 'segment',
						'priority' => 600,
						'options' => [
							'route'    => '/metrics/adwords[/:action][/:id]/',
							'constraints' => [
								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
								'id'     => '[0-9]+',
							],
							'defaults' => [
								'module'     => 'Metrics',
								'section'    => 'Adwords',
								'controller' => 'MetricsAdmin\Controller\Adwords',
								'action'     => 'index',
								'side'       => 'admin'
							],
						],
					],
					'metrics' => [
						'type'    => 'segment',
						'priority' => 600,
						'options' => [
							'route'    => '/metrics/metrics[/:action][/:id]/',
							'constraints' => [
								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
								'id'     => '[0-9]+',
							],
							'defaults' => [
                                'module'     => 'Metrics',
                                'section'    => 'Metrics',
								'controller' => 'MetricsAdmin\Controller\Metrics',
								'action'     => 'index',
								'side'       => 'admin'
							],
						],
					],
					'cash' => [
						'type'    => 'segment',
						'priority' => 600,
						'options' => [
							'route'    => '/metrics/cash[/:action][/:id]/',
							'constraints' => [
								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
								'id'     => '[0-9]+',
							],
							'defaults' => [
								'module'     => 'Metrics',
								'section'    => 'Cash',
								'controller' => 'MetricsAdmin\Controller\Cash',
								'action'     => 'index',
								'side'       => 'admin'
							],
						],
					],
				],
			],
            
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'metrics' => __DIR__ . '/../view',
        ],
    ],
];