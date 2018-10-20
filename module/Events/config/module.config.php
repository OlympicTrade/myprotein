<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Events\Controller\Events' => 'Events\Controller\EventsController',
            'Admin\Controller\Events' => 'EventsAdmin\Controller\EventsController',
        ),
    ),
    'router' => array(
        'routes' => array(
            /*'events' => array(
                'type'    => 'segment',
                'priority' => 500,
                'options' => array(
                    'route'    => '[/:locale]/events[/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z][a-zA-Z0-9_-]+',
                    ),
                    'defaults' => array(
                        'module'     => 'Events',
                        'section'    => 'Events',
                        'controller' => 'Events\Controller\Events',
                        'action'     => 'index',
                    ),
                ),
            ),*/
            'adminEvents' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/events/events[/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module'     => 'Events',
                        'section'    => 'Events',
                        'controller' => 'Admin\Controller\Events',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'events' => __DIR__ . '/../view',
            'admin' => __DIR__ . '/../view',
        ),
    ),
);