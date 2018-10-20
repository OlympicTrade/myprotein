<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Athletes\Controller\Mobile'   => 'Athletes\Controller\MobileController',
            'Athletes\Controller\Athletes'      => 'Athletes\Controller\AthletesController',
            'AthletesAdmin\Controller\Athletes' => 'AthletesAdmin\Controller\AthletesController',
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
                    'athletes' => array(
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => array(
                            'route'    => '/athletes/',
                            'defaults' => array(
                                'module'     => 'Athletes',
                                'section'    => 'Athletes',
                                'controller' => 'Athletes\Controller\Mobile',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                    'athlete' => array(
                        'type'    => 'segment',
                        'priority' => 400,
                        'options' => array(
                            'route'    => '/athletes/:url/',
                            'defaults' => array(
                                'module'     => 'Athletes',
                                'section'    => 'Athletes',
                                'controller' => 'Athletes\Controller\Mobile',
                                'action'     => 'athlete',
                            ),
                        ),
                    ),
                ),
            ),
            'athletes' => array(
                'type'    => 'segment',
                'priority' => 500,
                'options' => array(
                    'route'    => '/athletes/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z0-9_-]+',
                    ),
                    'defaults' => array(
                        'module'     => 'Athletes',
                        'section'    => 'Athletes',
                        'controller' => 'Athletes\Controller\Athletes',
                        'action'     => 'index',
                    ),
                ),
            ),
            'athlete' => array(
                'type'    => 'segment',
                'priority' => 500,
                'options' => array(
                    'route'    => '/athletes/:url/',
                    'defaults' => array(
                        'module'     => 'Athletes',
                        'section'    => 'Athletes',
                        'controller' => 'Athletes\Controller\Athletes',
                        'action'     => 'athlete',
                    ),
                ),
            ),
            'adminAthletes' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/athletes/athletes[/:action][/:id]/',
                    'defaults' => array(
                        'module'     => 'Athletes',
                        'section'    => 'Athletes',
                        'controller' => 'AthletesAdmin\Controller\Athletes',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'athletes' => __DIR__ . '/../view',
            'admin' => __DIR__ . '/../view',
        ),
    ),
);