<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'News\Controller\News' => 'News\Controller\NewsController',
            'Admin\Controller\News' => 'NewsAdmin\Controller\NewsController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'news' => array(
                'type'    => 'segment',
                'priority' => 500,
                'options' => array(
                    'route'    => '[/:locale]/news/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z0-9_-]+',
                    ),
                    'defaults' => array(
                        'module'     => 'News',
                        'section'    => 'News',
                        'controller' => 'News\Controller\News',
                        'action'     => 'index',
                    ),
                ),
            ),
            'newsView' => array(
                'type'    => 'segment',
                'priority' => 400,
                'options' => array(
                    'route'    => '/news/:url/',
                    'defaults' => array(
                        'module'     => 'News',
                        'section'    => 'News',
                        'controller' => 'News\Controller\News',
                        'action'     => 'view',
                    ),
                ),
            ),
            'adminNews' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/news/news[/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module'     => 'News',
                        'section'    => 'News',
                        'controller' => 'Admin\Controller\News',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'news' => __DIR__ . '/../view',
            'admin' => __DIR__ . '/../view',
        ),
    ),
);