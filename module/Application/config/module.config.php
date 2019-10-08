<?php
namespace Application;

$develop = getenv('APPLICATION_ENV') == 'dev';

return array(
    'controllers' => array(
        'invokables' => array(
            __NAMESPACE__ . '\Controller\Mobile' => __NAMESPACE__ . '\Controller\MobileController',
            __NAMESPACE__ . '\Controller\Index' => __NAMESPACE__ . '\Controller\IndexController',
            __NAMESPACE__ . '\Controller\Error' => __NAMESPACE__ . '\Controller\ErrorController',
            'Admin\Controller\Index'       => __NAMESPACE__ . 'Admin\Controller\IndexController',
            'Admin\Controller\Service'     => __NAMESPACE__ . 'Admin\Controller\ServiceController',
            'Admin\Controller\Page'        => __NAMESPACE__ . 'Admin\Controller\PageController',
            'Admin\Controller\Settings'    => __NAMESPACE__ . 'Admin\Controller\SettingsController',
            'Admin\Controller\Menu'        => __NAMESPACE__ . 'Admin\Controller\MenuController',
            'Admin\Controller\MenuItems'   => __NAMESPACE__ . 'Admin\Controller\MenuItemsController',
            'Admin\Controller\Content'     => __NAMESPACE__ . 'Admin\Controller\ContentController',
            'Admin\Controller\Regions'     => __NAMESPACE__ . 'Admin\Controller\RegionsController',
        ),
    ),
    'asset_manager' => array(
        'resolver_configs' => array(
            'paths' => array(
                __DIR__ . '/../public',
            ),
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
                    'home' => array(
                        'type' => 'literal',
                        'priority' => 500,
                        'options' => array(
                            'route' => '/',
                            'defaults' => array(
                                'controller' => __NAMESPACE__ . '\Controller\Mobile',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'textPage' => array(
                        'type' => 'segment',
                        'priority' => 100,
                        'options' => array(
                            'route' => '/:path',
                            'constraints' => array(
                                'path' => '.*',
                            ),
                            'defaults' => array(
                                'controller' => __NAMESPACE__ . '\Controller\Mobile',
                                'action'     => 'page',
                            )
                        )
                    ),
                    'about' => array(
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'priority' => 500,
                        'options' => array(
                            'route'    => '/about/',
                            'defaults' => array(
                                'controller' => __NAMESPACE__ . '\Controller\Mobile',
                                'action'     => 'about',
                                'module'     => 'Application',
                                'section'    => 'Page',
                            ),
                        ),
                    ),
                ),
            ),
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'priority' => 500,
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => __NAMESPACE__ . '\Controller\Index',
                        'action'     => 'index',
                        'module'     => 'Application',
                        'section'    => 'Page',
                    ),
                ),
            ),
            'about' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'priority' => 500,
                'options' => array(
                    'route'    => '/about/',
                    'defaults' => array(
                        'controller' => __NAMESPACE__ . '\Controller\Index',
                        'action'     => 'about',
                        'module'     => 'Application',
                        'section'    => 'Page',
                    ),
                ),
            ),
            'sitemap' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'priority' => 500,
                'options' => array(
                    'route'    => '/sitemap/',
                    'defaults' => array(
                        'controller' => __NAMESPACE__ . '\Controller\Index',
                        'action'     => 'sitemap',
                        'module'     => 'Application',
                        'section'    => 'Page',
                    ),
                ),
            ),
            'robots' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'priority' => 500,
                'options' => array(
                    'route'    => '/robots/',
                    'defaults' => array(
                        'controller' => __NAMESPACE__ . '\Controller\Index',
                        'action'     => 'robots',
                        'module'     => 'Application',
                        'section'    => 'Page',
                    ),
                ),
            ),
            'adminIndex' => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'priority' => 1000,
                'options' => array(
                    'route'    => '/admin/',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Index',
                        'action'     => 'index',
                        'side'       => 'admin',
                    ),
                ),
            ),
            'admin' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/:module/:section[/:action][/:id]/',
                    'constraints' => array(
                        'module' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'section' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module'     => 'application',
                        'section'    => 'page',
                        'controller' => 'Admin\Controller\Page',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ),
                ),
            ),
            'adminPage' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/application/page[/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module'     => 'application',
                        'section'    => 'page',
                        'controller' => 'Admin\Controller\Page',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ),
                ),
            ),
            'adminRegions' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/application/regions[/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module'     => 'application',
                        'section'    => 'regions',
                        'controller' => 'Admin\Controller\Regions',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ),
                ),
            ),
            'adminSettings' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/application/settings[/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module'     => 'application',
                        'section'    => 'settings',
                        'controller' => 'Admin\Controller\Settings',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ),
                ),
            ),
            'adminMenu' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/application/menu[/:action]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'module'     => 'application',
                        'section'    => 'menu',
                        'controller' => 'Admin\Controller\Menu',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ),
                ),
            ),
            'adminMenuItems' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/application/menu-items[/:action]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'module'     => 'application',
                        'section'    => 'menu-items',
                        'controller' => 'Admin\Controller\MenuItems',
                        'action'     => 'index',
                        'side'       => 'admin',
                    ),
                ),
            ),
            'adminContentItems' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/application/content[/:action]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'module'     => 'application',
                        'section'    => 'content',
                        'controller' => 'Admin\Controller\Content',
                        'action'     => 'index',
                        'side'       => 'admin',
                    ),
                ),
            ),
            'adminService' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/application/service[/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module'     => 'application',
                        'section'    => 'page',
                        'controller' => 'Admin\Controller\Service',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ),
                ),
            ),
            'admin' => array(
                'type'    => 'segment',
                'priority' => 500,
                'options' => array(
                    'route'    => '/admin[/:module][/:section][/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Index',
                        'action'     => 'index',
                        'side'       => 'admin',
                    ),
                ),
            ),
            'error' => array(
                'type' => 'Literal',
                'priority' => 200,
                'options' => array(
                    'route' => '/error/',
                    'defaults' => array(
                        'controller' => __NAMESPACE__ . '\Controller\Error',
                        'action'     => 'index',
                    )
                )
            ),
            'textPage' => array(
                'type' => 'segment',
                'priority' => 100,
                'options' => array(
                    'route' => '/:path',
                    'constraints' => array(
                        'path' => '.*',
                    ),
                    'defaults' => array(
                        'controller' => __NAMESPACE__ . '\Controller\Index',
                        'action'     => 'page',
                    )
                )
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
        ),
    ),
    'translator' => array(
        'locale' => 'ru_RU',
        'translation_file_patterns' => array(
            array(
                'type'     => 'phparray',
                'base_dir' => __DIR__ . '/../languages',
                'pattern'  => '%s.php',
            ),
            array(
                'type'     => 'phparray',
                'base_dir' => __DIR__ . '/../languages/' . __NAMESPACE__ . '/',
                'pattern'  => '%s.php',
                'text_domain' => __NAMESPACE__,
            ),
            array(
                'type'     => 'phparray',
                'base_dir' => __DIR__ . '/../languages/Forms/',
                'pattern'  => '%s.php',
                'text_domain' => 'Forms',
            ),
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => $develop,
        'display_exceptions'       => $develop,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/not-found',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/main.phtml',
            'pagination-slide'        => __DIR__ . '/../view/pagination/slide.phtml',
            'admin-pagination-slide'  => __DIR__ . '/../view/pagination/admin-slide.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
);
