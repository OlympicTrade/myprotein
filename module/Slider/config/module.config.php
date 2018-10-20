<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Slider\Controller\Slider' => 'Slider\Controller\SliderController',
            'Admin\Controller\Slider' => 'SliderAdmin\Controller\SliderController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'adminSlider' => array(
                'type'    => 'segment',
                'priority' => 600,
                'options' => array(
                    'route'    => '/admin/slider/slider[/:action][/:id]/',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module'     => 'Slider',
                        'section'    => 'Slider',
                        'controller' => 'Admin\Controller\Slider',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'slider' => __DIR__ . '/../view',
            'admin' => __DIR__ . '/../view',
        ),
    ),
);