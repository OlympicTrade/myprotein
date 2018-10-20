<?php
return [
    'controllers' => [
        'invokables' => [
            'Balance\Controller\Balance'          => 'Balance\Controller\BalanceController',
            'BalanceAdmin\Controller\Cash'        => 'BalanceAdmin\Controller\CashController',
        ],
    ],
    'router' => [
        'routes' => [
            
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'balance' => __DIR__ . '/../view',
        ],
    ],
];