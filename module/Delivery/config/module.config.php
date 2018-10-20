<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Delivery\Controller\Delivery'  => 'Delivery\Controller\DeliveryController',
            'Delivery\Controller\MobileDelivery'  => 'Delivery\Controller\MobileDeliveryController',
            'Admin\Controller\Delivery'     => 'DeliveryAdmin\Controller\DeliveryController',
            'Admin\Controller\Points'       => 'DeliveryAdmin\Controller\PointsController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'mobile' => array(
                'type' => 'Hostname',
                'priority' => 600,
                'options' => array(
                    'route' => 'm.:domain',
                    'constraints' => array('domain' => '.*',),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'delivery' => array(
                        'type'    => 'segment',
                        'priority' => 600,
                        'options' => array(
                            'route'    => '/delivery/',
                            'defaults' => array(
                                'module'     => 'Delivery',
                                'section'    => 'Delivery',
                                'controller' => 'Delivery\Controller\MobileDelivery',
                                'action'     => 'index',
                            ),
                        ),
                    ),
					'regions' => array(
						'type' => 'Zend\Mvc\Router\Http\Literal',
						'priority' => 600,
						'options' => array(
							'route'    => '/regions/',
							'defaults' => array(
								'controller' => 'Delivery\Controller\MobileDelivery',
								'action'     => 'regions',
								'module'     => 'Delivery',
								'section'    => 'Delivery',
							),
						),
					),
                    'deliveryDefault' => array(
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => array(
                            'route'    => '/delivery/:action/',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                'module'     => 'Delivery',
                                'section'    => 'Delivery',
                                'controller' => 'Delivery\Controller\Delivery',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                ),
            ),
            'deliveryDefault' => array(
                'type'    => 'segment',
                'priority' => 500,
                'options' => array(
                    'route'    => '/delivery[/:action]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'module'     => 'Delivery',
                        'section'    => 'Delivery',
                        'controller' => 'Delivery\Controller\Delivery',
                        'action'     => 'index',
                    ),
                ),
            ),
            'regions' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'priority' => 500,
                'options' => array(
                    'route'    => '/regions/',
                    'defaults' => array(
                        'controller' => 'Delivery\Controller\Delivery',
                        'action'     => 'regions',
                        'module'     => 'Delivery',
                        'section'    => 'Delivery',
                    ),
                ),
            ),
            'adminDelivery' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/delivery/delivery[/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module'     => 'Delivery',
                        'section'    => 'Delivery',
                        'controller' => 'Admin\Controller\Delivery',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ),
                ),
            ),
            'adminDeliveryPoints' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/delivery/points[/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module'     => 'Delivery',
                        'section'    => 'Points',
                        'controller' => 'Admin\Controller\Points',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'delivery' => __DIR__ . '/../view',
            'admin' => __DIR__ . '/../view',
        ),
    ),
);