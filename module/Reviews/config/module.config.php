<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Reviews\Controller\Reviews' => 'Reviews\Controller\ReviewsController',
            'Admin\Controller\Reviews' => 'ReviewsAdmin\Controller\ReviewsController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'reviews' => array(
                'type'    => 'segment',
                'priority' => 500,
                'options' => array(
                    'route'    => '[/:locale]/reviews[/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z0-9_-]+',
                    ),
                    'defaults' => array(
                        'module'     => 'Reviews',
                        'section'    => 'Reviews',
                        'controller' => 'Reviews\Controller\Reviews',
                        'action'     => 'index',
                    ),
                ),
            ),
            'addReview' => array(
                'type'    => 'segment',
                'priority' => 400,
                'options' => array(
                    'route'    => '/review/add-review/',
                    'defaults' => array(
                        'module'     => 'Reviews',
                        'section'    => 'Reviews',
                        'controller' => 'Reviews\Controller\Reviews',
                        'action'     => 'add-review',
                    ),
                ),
            ),
            'adminReviews' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/reviews/reviews[/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module'     => 'Reviews',
                        'section'    => 'Reviews',
                        'controller' => 'Admin\Controller\Reviews',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'reviews' => __DIR__ . '/../view',
            'admin' => __DIR__ . '/../view',
        ),
    ),
);