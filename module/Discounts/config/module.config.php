<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Discounts\Controller\Discounts'      => 'Discounts\Controller\DiscountsController',
            'DiscountsAdmin\Controller\Discounts' => 'DiscountsAdmin\Controller\DiscountsController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'discount' => array(
                'type'    => 'segment',
                'priority' => 400,
                'options' => array(
                    'route'    => '/update-discount/',
                    'defaults' => array(
                        'module'     => 'Discount',
                        'section'    => 'Discount',
                        'controller' => 'Discounts\Controller\Discounts',
                        'action'     => 'updateDiscount',
                    ),
                ),
            ),
            'adminDiscounts' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/discounts/discounts[/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module'     => 'Discounts',
                        'section'    => 'Discounts',
                        'controller' => 'DiscountsAdmin\Controller\Discounts',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'discounts' => __DIR__ . '/../view',
            'admin' => __DIR__ . '/../view',
        ),
    ),
);