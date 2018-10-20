<?php
return [
    'controllers' => [
        'invokables' => [
            'ManagerAdmin\Controller\Manager'  => 'ManagerAdmin\Controller\ManagerController',
        ],
    ],
    'router' => [
        'routes' => [
            'managerSync' => array(
                'type'    => 'segment',
                'priority' => 400,
                'options' => array(
                    'route'    => '/manager/sync/',
                    'constraints' => array(
                        'action' => '.*',
                    ),
                    'defaults' => array(
                        'module'     => 'Manager',
                        'section'    => 'Manager',
                        'controller' => 'ManagerAdmin\Controller\Manager',
                        'action'     => 'sync',
                    ),
                ),
            ),
        ]
    ]
];