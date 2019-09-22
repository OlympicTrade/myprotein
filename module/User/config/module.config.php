<?php
namespace User;

return [
    'controllers' => [
        'invokables' => [
            'User\Controller\User'          => 'User\Controller\UserController',
            'User\Controller\MobileUser'    => 'User\Controller\MobileUserController',
            'UserAdmin\Controller\User'     => 'UserAdmin\Controller\UserController',
            'UserAdmin\Controller\Phones'   => 'UserAdmin\Controller\PhonesController',
        ],
    ],
    'router' => [
        'routes' => [
            'mobile' => [
                'type' => 'Hostname',
                'priority' => 600,
                'options' => [
                    'route' => 'm.:domain',
                    'constraints' => ['domain' => '.*',],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'userMobileIndex' => [
                        'type'    => 'literal',
                        'priority' => 600,
                        'options' => [
                            'route'    => '/user/',
                            'defaults' => [
                                'module'     => 'User',
                                'section'    => 'User',
                                'controller' => 'User\Controller\MobileUser',
                                'action'     => 'index',
                            ],
                        ],
                    ],
                    'userMobileConfirm' => [
                        'type'    => 'segment',
                        'priority' => 600,
                        'options' => [
                            'route'    => '/user/confirm/',
                            'defaults' => [
                                'module'     => 'User',
                                'section'    => 'User',
                                'controller' => 'User\Controller\MobileUser',
                                'action'     => 'confirm',
                            ],
                        ],
                    ],
                    'user' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/user/[:action][/:id]/',
                            'constraints' => [
                                'locale' => '[a-z]{2}',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[a-zA-Z][a-zA-Z0-9_-]+',
                            ],
                            'defaults' => [
                                'module'     => 'User',
                                'section'    => 'User',
                                'controller' => 'User\Controller\User',
                                'action'     => 'index',
                            ],
                        ],
                    ],
                    /*'login' => [
                        'type'    => 'literal',
                        'priority' => 600,
                        'options' => [
                            'route'    => '/login/',
                            'defaults' => [
                                'module'     => 'User',
                                'section'    => 'User',
                                'controller' => 'User\Controller\User',
                                'action'     => 'login',
                            ],
                        ],
                    ],
                    'logout' => [
                        'type'    => 'literal',
                        'priority' => 600,
                        'options' => [
                            'route'    => '/logout/',
                            'defaults' => [
                                'module'     => 'User',
                                'section'    => 'User',
                                'controller' => 'User\Controller\User',
                                'action'     => 'logout',
                            ],
                        ],
                    ],
                    'registration' => [
                        'type'    => 'literal',
                        'priority' => 600,
                        'options' => [
                            'route'    => '/registration/',
                            'defaults' => [
                                'module'     => 'User',
                                'section'    => 'User',
                                'controller' => 'User\Controller\User',
                                'action'     => 'registration',
                            ],
                        ],
                    ],
                    'remind' => [
                        'type'    => 'literal',
                        'priority' => 600,
                        'options' => [
                            'route'    => '/remind/',
                            'defaults' => [
                                'module'     => 'User',
                                'section'    => 'User',
                                'controller' => 'User\Controller\User',
                                'action'     => 'remind',
                            ],
                        ],
                    ],*/
                ],
            ],
            'user' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/user[/:action][/:id]/',
                    'constraints' => [
                        'locale' => '[a-z]{2}',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z][a-zA-Z0-9_-]+',
                    ],
                    'defaults' => [
                        'module'     => 'User',
                        'section'    => 'User',
                        'controller' => 'User\Controller\User',
                        'action'     => 'index',
                    ],
                ],
            ],
            'login' => [
                'type'    => 'literal',
                'priority' => 1000,
                'options' => [
                    'route'    => '/login/',
                    'defaults' => [
                        'module'     => 'User',
                        'section'    => 'User',
                        'controller' => 'User\Controller\User',
                        'action'     => 'login',
                    ],
                ],
            ],
            'logout' => [
                'type'    => 'literal',
                'priority' => 1000,
                'options' => [
                    'route'    => '/logout/',
                    'defaults' => [
                        'module'     => 'User',
                        'section'    => 'User',
                        'controller' => 'User\Controller\User',
                        'action'     => 'logout',
                    ],
                ],
            ],
            'registration' => [
                'type'    => 'literal',
                'priority' => 1000,
                'options' => [
                    'route'    => '/registration/',
                    'defaults' => [
                        'module'     => 'User',
                        'section'    => 'User',
                        'controller' => 'User\Controller\User',
                        'action'     => 'registration',
                    ],
                ],
            ],
            'remind' => [
                'type'    => 'literal',
                'priority' => 1000,
                'options' => [
                    'route'    => '/remind/',
                    'defaults' => [
                        'module'     => 'User',
                        'section'    => 'User',
                        'controller' => 'User\Controller\User',
                        'action'     => 'remind',
                    ],
                ],
            ],
            'adminUser' => [
                'type'    => 'segment',
                'priority' => 600,
                'options' => [
                    'route'    => '/admin/user/user[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'module'     => 'User',
                        'section'    => 'User',
                        'controller' => 'UserAdmin\Controller\User',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ],
                ],
            ],
            'adminPhones' => [
                'type'    => 'segment',
                'priority' => 600,
                'options' => [
                    'route'    => '/admin/user/phones[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'module'     => 'User',
                        'section'    => 'Phones',
                        'controller' => 'UserAdmin\Controller\Phones',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ],
                ],
            ],
        ],
    ],
    'translator' => [
        'locale' => 'ru_RU',
        'translation_file_patterns' => [
            [
                'type'     => 'phparray',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.php',
                'text_domain' => __NAMESPACE__,
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'user' => __DIR__ . '/../view',
            'admin' => __DIR__ . '/../view',
        ],
    ],
    'images' => [
        'user' => [
            'resolutions' => [
                'r'  => ['width' => 200, 'height' => 200],
                'hr' => ['width' => 800, 'height' => 600],
            ],
        ],
    ],
    'di' => [
        'instance' => [
            'User\Event\Auth' => [
                'parameters' => [
                    'aclClass'       => 'User\Acl\Acl'
                ],
            ],

        ],
    ],
];