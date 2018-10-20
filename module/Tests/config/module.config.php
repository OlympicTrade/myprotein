<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Tests\Controller\Tests'      => 'Tests\Controller\TestsController',
            'TestsAdmin\Controller\Tests' => 'TestsAdmin\Controller\TestsController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'tests' => array(
                'type'    => 'segment',
                'priority' => 500,
                'options' => array(
                    'route'    => '[/:locale]/tests[/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z0-9_-]+',
                    ),
                    'defaults' => array(
                        'module'     => 'Tests',
                        'section'    => 'Tests',
                        'controller' => 'Tests\Controller\Tests',
                        'action'     => 'index',
                    ),
                ),
            ),
            'adminTests' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/tests/tests[/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module'     => 'Tests',
                        'section'    => 'Tests',
                        'controller' => 'TestsAdmin\Controller\Tests',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'tests' => __DIR__ . '/../view',
            'admin' => __DIR__ . '/../view',
        ),
    ),
);