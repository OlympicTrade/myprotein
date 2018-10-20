<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Mainpage\Controller\Mainpage' => 'Mainpage\Controller\MainpageController',
            'Admin\Controller\Mainpage' => 'MainpageAdmin\Controller\MainpageController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'adminMainpage' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/mainpage/mainpage[/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module'     => 'Mainpage',
                        'section'    => 'Mainpage',
                        'controller' => 'Admin\Controller\Mainpage',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'mainpage' => __DIR__ . '/../view',
            'admin' => __DIR__ . '/../view',
        ),
    ),
);