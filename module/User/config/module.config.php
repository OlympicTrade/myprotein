<?php
namespace User;

return array(
    'controllers' => array(
        'invokables' => array(
            'User\Controller\User' => 'User\Controller\UserController',
            'UserAdmin\Controller\User' => 'UserAdmin\Controller\UserController',
            'UserAdmin\Controller\Phones' => 'UserAdmin\Controller\PhonesController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'user' => array(
                'type'    => 'segment',
                'priority' => 500,
                'options' => array(
                    'route'    => '/user[/:action][/:id]/',
                    'constraints' => array(
                        'locale' => '[a-z]{2}',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z][a-zA-Z0-9_-]+',
                    ),
                    'defaults' => array(
                        'module'     => 'User',
                        'section'    => 'User',
                        'controller' => 'User\Controller\User',
                        'action'     => 'index',
                    ),
                ),
            ),
            'login' => array(
                'type'    => 'literal',
                'priority' => 500,
                'options' => array(
                    'route'    => '/login/',
                    'defaults' => array(
                        'module'     => 'User',
                        'section'    => 'User',
                        'controller' => 'User\Controller\User',
                        'action'     => 'login',
                    ),
                ),
            ),
            'logout' => array(
                'type'    => 'literal',
                'priority' => 500,
                'options' => array(
                    'route'    => '/logout/',
                    'defaults' => array(
                        'module'     => 'User',
                        'section'    => 'User',
                        'controller' => 'User\Controller\User',
                        'action'     => 'logout',
                    ),
                ),
            ),
            'registration' => array(
                'type'    => 'literal',
                'priority' => 500,
                'options' => array(
                    'route'    => '/registration/',
                    'defaults' => array(
                        'module'     => 'User',
                        'section'    => 'User',
                        'controller' => 'User\Controller\User',
                        'action'     => 'registration',
                    ),
                ),
            ),
            'remind' => array(
                'type'    => 'literal',
                'priority' => 500,
                'options' => array(
                    'route'    => '/remind/',
                    'defaults' => array(
                        'module'     => 'User',
                        'section'    => 'User',
                        'controller' => 'User\Controller\User',
                        'action'     => 'remind',
                    ),
                ),
            ),
            'adminUser' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/user/user[/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module'     => 'User',
                        'section'    => 'User',
                        'controller' => 'UserAdmin\Controller\User',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ),
                ),
            ),
            'adminPhones' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/user/phones[/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module'     => 'User',
                        'section'    => 'Phones',
                        'controller' => 'UserAdmin\Controller\Phones',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ),
                ),
            ),
        ),
    ),
    'translator' => array(
        'locale' => 'ru_RU',
        'translation_file_patterns' => array(
            array(
                'type'     => 'phparray',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.php',
                'text_domain' => __NAMESPACE__,
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'user' => __DIR__ . '/../view',
            'admin' => __DIR__ . '/../view',
        ),
    ),
    'images' => array(
        'user' => array(
            'resolutions' => array(
                'r'  => array('width' => 200, 'height' => 200),
                'hr' => array('width' => 800, 'height' => 600),
            ),
        )
    ),
    'di' => array(
        'instance' => array(
            'User\Event\Auth' => array(
                'parameters' => array(
                    'aclClass'       => 'User\Acl\Acl'
                )
            ),

        )
    ),
);