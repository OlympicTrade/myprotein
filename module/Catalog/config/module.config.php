<?php
return [
    'controllers' => [
        'invokables' => [
            'Catalog\Controller\Catalog' => 'Catalog\Controller\CatalogController',
            'Catalog\Controller\Orders'  => 'Catalog\Controller\OrdersController',
            'Catalog\Controller\Payment' => 'Catalog\Controller\PaymentController',
            'Catalog\Controller\Sync'    => 'Catalog\Controller\SyncController',
            'Catalog\Controller\Parser'  => 'Catalog\Controller\ParserController',
            'Catalog\Controller\Yandex'  => 'Catalog\Controller\YandexController',
            'CatalogAdmin\Controller\Catalog'   => 'CatalogAdmin\Controller\CatalogController',
            'CatalogAdmin\Controller\Products'  => 'CatalogAdmin\Controller\ProductsController',
            'CatalogAdmin\Controller\Brands'    => 'CatalogAdmin\Controller\BrandsController',
            'CatalogAdmin\Controller\Orders'    => 'CatalogAdmin\Controller\OrdersController',
            'CatalogAdmin\Controller\Reviews'   => 'CatalogAdmin\Controller\ReviewsController',
            'CatalogAdmin\Controller\Requests'  => 'CatalogAdmin\Controller\RequestsController',
            'CatalogAdmin\Controller\Supplies'  => 'CatalogAdmin\Controller\SuppliesController',
            'Catalog\Controller\MobileCatalog'  => 'Catalog\Controller\MobileCatalogController',
            'Catalog\Controller\MobileOrders'   => 'Catalog\Controller\MobileOrdersController',
            'Catalog\Controller\MobilePayment'  => 'Catalog\Controller\MobilePaymentController',
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
                    'orderPayment' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/payment[/:action]/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Products',
                                'controller' => 'Catalog\Controller\MobilePayment',
                                'action'     => 'payment',
                            ],
                        ],
                    ],
                    'catalog' => [
                        'type'    => 'segment',
                        'priority' => 400,
                        'options' => [
                            'route'    => '/catalog[/:url]/',
                            'constraints' => [
                                'url' => '.*',
                            ],
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Catalog',
                                'controller' => 'Catalog\Controller\MobileCatalog',
                                'action'     => 'index',
                            ],
                        ],
                    ],
                    'ajaxProducts' => [
                        'type'    => 'segment',
                        'priority' => 400,
                        'options' => [
                            'route'    => '/catalog/ajax-products/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Catalog',
                                'controller' => 'Catalog\Controller\MobileCatalog',
                                'action'     => 'ajax-products',
                            ],
                        ],
                    ],
                    'cart' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/cart/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Orders',
                                'controller' => 'Catalog\Controller\MobileOrders',
                                'action'     => 'cart',
                            ],
                        ],
                    ],
                    'cartInfo' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/cart/get-info/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Orders',
                                'controller' => 'Catalog\Controller\Orders',
                                'action'     => 'cartInfo',
                            ],
                        ],
                    ],
                    'deliveryInfo' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/order/delivery-info/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Orders',
                                'controller' => 'Catalog\Controller\Orders',
                                'action'     => 'deliveryInfo',
                            ],
                        ],
                    ],
                    'ecommerceInfo' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/order/ecommerce-info/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Orders',
                                'controller' => 'Catalog\Controller\Orders',
                                'action'     => 'ecommerceInfo',
                            ],
                        ],
                    ],
                    'productRequestFrom' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/order/product-request/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Orders',
                                'controller' => 'Catalog\Controller\Orders',
                                'action'     => 'product-request',
                            ],
                        ],
                    ],
                    'getProductInfo' => [
                        'type'    => 'segment',
                        'priority' => 400,
                        'options' => [
                            'route'    => '/catalog/get-product-info/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Catalog',
                                'controller' => 'Catalog\Controller\Catalog',
                                'action'     => 'getProductInfo',
                            ],
                        ],
                    ],
                    'getProductStock' => [
                        'type'    => 'segment',
                        'priority' => 400,
                        'options' => [
                            'route'    => '/catalog/get-product-stock/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Catalog',
                                'controller' => 'Catalog\Controller\Catalog',
                                'action'     => 'getProductStock',
                            ],
                        ],
                    ],
                    'productGetPrice' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/catalog/get-price/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Products',
                                'controller' => 'Catalog\Controller\Catalog',
                                'action'     => 'getPrice',
                            ],
                        ],
                    ],
                    'catalogSearch' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/catalog/search/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Catalog',
                                'controller' => 'Catalog\Controller\Catalog',
                                'action'     => 'search',
                            ],
                        ],
                    ],
                    'product' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/goods/:url[/:tab]/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Products',
                                'controller' => 'Catalog\Controller\MobileCatalog',
                                'action'     => 'product',
                            ],
                        ],
                    ],
                    'orderStatus' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/order/order-status/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Orders',
                                'controller' => 'Catalog\Controller\Orders',
                                'action'     => 'orderStatus',
                            ],
                        ],
                    ],
                    'order' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/order/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Orders',
                                'controller' => 'Catalog\Controller\Orders',
                                'action'     => 'order',
                            ],
                        ],
                    ],
                    'catalogCartFrom' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/order/cart-form/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Orders',
                                'controller' => 'Catalog\Controller\Orders',
                                'action'     => 'cart-form',
                            ],
                        ],
                    ],
					'productAddReview' => [
						'type'    => 'segment',
						'priority' => 400,
						'options' => [
							'route'    => '/catalog/add-review/',
							'defaults' => [
								'module'     => 'Catalog',
								'section'    => 'Catalog',
								'controller' => 'Catalog\Controller\Catalog',
								'action'     => 'add-review',
							],
						],
					],
                    'orders' => [
                        'type'    => 'literal',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/orders/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Orders',
                                'controller' => 'Catalog\Controller\Orders',
                                'action'     => 'orders',
                            ],
                        ],
                    ],
                    'orderCart' => [
                        'type'    => 'segment',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/order/cart/:id/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Orders',
                                'controller' => 'Catalog\Controller\Orders',
                                'action'     => 'orderCart',
                            ],
                        ],
                    ],
                ],
            ],
            'orderStatus' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/order/order-status/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Orders',
                        'controller' => 'Catalog\Controller\Orders',
                        'action'     => 'orderStatus',
                    ],
                ],
            ],
            'catalogParser' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/catalog/parser/:action/',
                    'constraints' => ['url' => '.*'],
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Parser',
                        'controller' => 'Catalog\Controller\Parser',
                    ],
                ],
            ],
            'catalog' => [
                'type'    => 'segment',
                'priority' => 400,
                'options' => [
                    'route'    => '/catalog[/:url]/',
                    'constraints' => ['url' => '.*'],
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Catalog',
                        'controller' => 'Catalog\Controller\Catalog',
                        'action'     => 'index',
                    ],
                ],
            ],
            'eventProducts' => [
                'type'    => 'segment',
                'priority' => 400,
                'options' => [
                    'route'    => '/catalog/event[/:event]/',
                    'constraints' => [
                        'url' => '.*',
                    ],
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Catalog',
                        'controller' => 'Catalog\Controller\Catalog',
                        'action'     => 'event',
                    ],
                ],
            ],
            'productGetPrice' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/catalog/get-price/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Products',
                        'controller' => 'Catalog\Controller\Catalog',
                        'action'     => 'get-price',
                    ],
                ],
            ],
            'orderPayment' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/payment[/:action]/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Products',
                        'controller' => 'Catalog\Controller\Payment',
                        'action'     => 'payment',
                    ],
                ],
            ],
            'product' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/goods/:url[/:tab]/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Products',
                        'controller' => 'Catalog\Controller\Catalog',
                        'action'     => 'product',
                    ],
                ],
            ],
            'popProducts' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/catalog/popular/:url/',
                    'constraints' => ['url' => '.*'],
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Products',
                        'controller' => 'Catalog\Controller\Catalog',
                        'action'     => 'popular-products',
                    ],
                ],
            ],
            'ajaxProducts' => [
                'type'    => 'segment',
                'priority' => 400,
                'options' => [
                    'route'    => '/catalog/ajax-products/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Catalog',
                        'controller' => 'Catalog\Controller\Catalog',
                        'action'     => 'ajaxProducts',
                    ],
                ],
            ],
            'getProductInfo' => [
                'type'    => 'segment',
                'priority' => 400,
                'options' => [
                    'route'    => '/catalog/get-product-info/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Catalog',
                        'controller' => 'Catalog\Controller\Catalog',
                        'action'     => 'getProductInfo',
                    ],
                ],
            ],
            'getProductStock' => [
                'type'    => 'segment',
                'priority' => 400,
                'options' => [
                    'route'    => '/catalog/get-product-stock/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Catalog',
                        'controller' => 'Catalog\Controller\Catalog',
                        'action'     => 'getProductStock',
                    ],
                ],
            ],
            'productAddReview' => [
                'type'    => 'segment',
                'priority' => 400,
                'options' => [
                    'route'    => '/catalog/add-review/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Catalog',
                        'controller' => 'Catalog\Controller\Catalog',
                        'action'     => 'add-review',
                    ],
                ],
            ],
            'catalogCartFrom' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/order/cart-form/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Orders',
                        'controller' => 'Catalog\Controller\Orders',
                        'action'     => 'cart-form',
                    ],
                ],
            ],
            'catalogRequestForm' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/order/request-form/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Orders',
                        'controller' => 'Catalog\Controller\Orders',
                        'action'     => 'request-form',
                    ],
                ],
            ],
            'productRequestFrom' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/order/product-request/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Orders',
                        'controller' => 'Catalog\Controller\Orders',
                        'action'     => 'product-request',
                    ],
                ],
            ],
            'catalogRecoProduct' => [
                'type'    => 'segment',
                'priority' => 600,
                'options' => [
                    'route'    => '/catalog/get-reco-product/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Products',
                        'controller' => 'Catalog\Controller\Catalog',
                        'action'     => 'getRecoProduct',
                    ],
                ],
            ],
            'yandexMarket' => [
                'type' => 'literal',
                'priority' => 500,
                'options' => [
                    'route' => '/yandex/market',
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'yandexMarket' => [
                        'type'    => 'literal',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/yml/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Catalog',
                                'controller' => 'Catalog\Controller\Yandex',
                                'action' => 'yml',
                            ],
                        ],
                    ],
                    'yandexApiCart' => [
                        'type'    => 'literal',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/api/cart/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Catalog',
                                'controller' => 'Catalog\Controller\Yandex',
                                'action' => 'apiCart',
                            ],
                        ],
                    ],
                    'yandexApiOrder' => [
                        'type'    => 'literal',
                        'priority' => 500,
                        'options' => [
                            'route'    => '/api/order/accept/',
                            'defaults' => [
                                'module'     => 'Catalog',
                                'section'    => 'Catalog',
                                'controller' => 'Catalog\Controller\Yandex',
                                'action' => 'apiOrderAccept',
                            ],
                        ],
                    ],
                ],
            ],
            'googleMerchant' => [
                'type'    => 'segment',
                'priority' => 600,
                'options' => [
                    'route'    => '/google-merchant/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Catalog',
                        'controller' => 'Catalog\Controller\Catalog',
                        'action'     => 'googleMerchant',
                    ],
                ],
            ],
            'catalogSearch' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/catalog/search/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Catalog',
                        'controller' => 'Catalog\Controller\Catalog',
                        'action'     => 'search',
                    ],
                ],
            ],
            'cart' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/cart/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Orders',
                        'controller' => 'Catalog\Controller\Orders',
                        'action'     => 'cart',
                    ],
                ],
            ],
            'cartInfo' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/cart/get-info/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Orders',
                        'controller' => 'Catalog\Controller\Orders',
                        'action'     => 'cartInfo',
                    ],
                ],
            ],
            'deliveryInfo' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/order/delivery-info/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Orders',
                        'controller' => 'Catalog\Controller\Orders',
                        'action'     => 'deliveryInfo',
                    ],
                ],
            ],
            'ecommerceInfo' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/order/ecommerce-info/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Orders',
                        'controller' => 'Catalog\Controller\Orders',
                        'action'     => 'ecommerceInfo',
                    ],
                ],
            ],
            'order' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/order/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Orders',
                        'controller' => 'Catalog\Controller\Orders',
                        'action'     => 'order',
                    ],
                ],
            ],
            'abandonedOrders' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/catalog/abandoned-orders/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Orders',
                        'controller' => 'Catalog\Controller\Orders',
                        'action'     => 'abandoned-orders',
                    ],
                ],
            ],
            'updateOrdersStatus' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/catalog/update-orders-status/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Orders',
                        'controller' => 'Catalog\Controller\Orders',
                        'action'     => 'update-orders-status',
                    ],
                ],
            ],
            'updatePopularity' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/catalog/update-popularity/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Catalog',
                        'controller' => 'Catalog\Controller\Catalog',
                        'action'     => 'update-popularity',
                    ],
                ],
            ],
            'orders' => [
                'type'    => 'literal',
                'priority' => 500,
                'options' => [
                    'route'    => '/orders/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Orders',
                        'controller' => 'Catalog\Controller\Orders',
                        'action'     => 'orders',
                    ],
                ],
            ],
            'orderCart' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/order/cart/:id/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Orders',
                        'controller' => 'Catalog\Controller\Orders',
                        'action'     => 'orderCart',
                    ],
                ],
            ],
            'paymentConfirm' => [
                'type'    => 'segment',
                'priority' => 500,
                'options' => [
                    'route'    => '/payment/confirm/',
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Payment',
                        'controller' => 'Catalog\Controller\Payment',
                        'action'     => 'confirm',
                    ],
                ],
            ],
            'adminCatalog' => [
                'type'    => 'segment',
                'priority' => 600,
                'options' => [
                    'route'    => '/admin/catalog/catalog[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Catalog',
                        'controller' => 'CatalogAdmin\Controller\Catalog',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ],
                ],
            ],
            'adminProductsReviews' => [
                'type'    => 'segment',
                'priority' => 600,
                'options' => [
                    'route'    => '/admin/catalog/reviews[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Reviews',
                        'controller' => 'CatalogAdmin\Controller\Reviews',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ],
                ],
            ],
            'adminProductsRequests' => [
                'type'    => 'segment',
                'priority' => 600,
                'options' => [
                    'route'    => '/admin/catalog/requests[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Requests',
                        'controller' => 'CatalogAdmin\Controller\Requests',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ],
                ],
            ],
            'adminProducts' => [
                'type'    => 'segment',
                'priority' => 600,
                'options' => [
                    'route'    => '/admin/catalog/products[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Products',
                        'controller' => 'CatalogAdmin\Controller\Products',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ],
                ],
            ],
            'adminBrands' => [
                'type'    => 'segment',
                'priority' => 600,
                'options' => [
                    'route'    => '/admin/catalog/brands[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Brands',
                        'controller' => 'CatalogAdmin\Controller\Brands',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ],
                ],
            ],
            'adminOrders' => [
                'type'    => 'segment',
                'priority' => 700,
                'options' => [
                    'route'    => '/admin/catalog/orders[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Orders',
                        'controller' => 'CatalogAdmin\Controller\Orders',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ],
                ],
            ],
            'adminSupplies' => [
                'type'    => 'segment',
                'priority' => 600,
                'options' => [
                    'route'    => '/admin/catalog/supplies[/:action][/:id]/',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'module'     => 'Catalog',
                        'section'    => 'Supplies',
                        'controller' => 'CatalogAdmin\Controller\Supplies',
                        'action'     => 'index',
                        'side'       => 'admin'
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'catalog' => __DIR__ . '/../view',
            'admin' => __DIR__ . '/../view',
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'catalogList' => 'Catalog\View\Helper\CatalogList',
        ],
    ],
];