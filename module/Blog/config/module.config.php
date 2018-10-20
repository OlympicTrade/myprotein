<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Blog\Controller\Mobile' => 'Blog\Controller\MobileController',
            'Blog\Controller\Blog' => 'Blog\Controller\BlogController',
            'Admin\Controller\Blog' => 'BlogAdmin\Controller\BlogController',
            'BlogAdmin\Controller\Comments' => 'BlogAdmin\Controller\CommentsController',
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
                    'blog' => array(
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => array(
                            'route'    => '/blog/',
                            'defaults' => array(
                                'module'     => 'Blog',
                                'section'    => 'Blog',
                                'controller' => 'Blog\Controller\Mobile',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                    'blogArticle' => array(
                        'type'    => 'segment',
                        'priority' => 400,
                        'options' => array(
                            'route'    => '/blog/:url/',
                            'defaults' => array(
                                'module'     => 'Blog',
                                'section'    => 'Blog',
                                'controller' => 'Blog\Controller\Mobile',
                                'action'     => 'article',
                            ),
                        ),
                    ),
                    'blogAddComment' => array(
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => array(
                            'route'    => '/blog/add-comment/',
                            'defaults' => array(
                                'module'     => 'Blog',
                                'section'    => 'Blog',
                                'controller' => 'Blog\Controller\Blog',
                                'action'     => 'addComment',
                            ),
                        ),
                    ),
                ),
            ),
            'blog' => array(
                'type'    => 'segment',
                'priority' => 500,
                'options' => array(
                    'route'    => '/blog/',
                    'defaults' => array(
                        'module'     => 'Blog',
                        'section'    => 'Blog',
                        'controller' => 'Blog\Controller\Blog',
                        'action'     => 'index',
                    ),
                ),
            ),
            'blogAddComment' => array(
                'type'    => 'segment',
                'priority' => 500,
                'options' => array(
                    'route'    => '/blog/add-comment/',
                    'defaults' => array(
                        'module'     => 'Blog',
                        'section'    => 'Blog',
                        'controller' => 'Blog\Controller\Blog',
                        'action'     => 'addComment',
                    ),
                ),
            ),
            'blogArticle' => array(
                'type'    => 'segment',
                'priority' => 400,
                'options' => array(
                    'route'    => '/blog/:url/',
                    'defaults' => array(
                        'module'     => 'Blog',
                        'section'    => 'Blog',
                        'controller' => 'Blog\Controller\Blog',
                        'action'     => 'article',
                    ),
                ),
            ),
            'adminBlog' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/blog/blog[/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module'     => 'Blog',
                        'section'    => 'Blog',
                        'controller' => 'Admin\Controller\Blog',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ),
                ),
            ),
            'adminBlogComments' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/blog/comments[/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module'     => 'Blog',
                        'section'    => 'Comments',
                        'controller' => 'BlogAdmin\Controller\Comments',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'blog' => __DIR__ . '/../view',
            'admin' => __DIR__ . '/../view',
        ),
    ),
);