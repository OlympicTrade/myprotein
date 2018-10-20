<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Tasks\Controller\Tasks' => 'Tasks\Controller\TasksController',
            'Admin\Controller\Tasks' => 'TasksAdmin\Controller\TasksController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'adminTasks' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/tasks/tasks[/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module'     => 'Tasks',
                        'section'    => 'Tasks',
                        'controller' => 'Admin\Controller\Tasks',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'tasks' => __DIR__ . '/../view',
            'admin' => __DIR__ . '/../view',
        ),
    ),
);