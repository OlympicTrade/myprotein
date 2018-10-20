<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Search\Controller\Search' => 'Search\Controller\SearchController',
            'Admin\Controller\Search' => 'SearchAdmin\Controller\SearchController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'search' => array(
                'type'    => 'segment',
                'priority' => 500,
                'options' => array(
                    'route'    => '/search[/:query]/',
                    'constraints' => array(
                        'query' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'module'     => 'Search',
                        'section'    => 'Search',
                        'controller' => 'Search\Controller\Search',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'search' => __DIR__ . '/../view',
            'admin' => __DIR__ . '/../view',
        ),
    ),
);