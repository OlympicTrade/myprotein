<?php

namespace Catalog;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;

class Module implements AutoloaderProviderInterface
{
    public function getViewHelperConfig() {
        return array(
            'invokables' => array(
                'adminBarcodes'         => 'CatalogAdmin\View\Helper\Barcodes',
                'adminSupplyCart'       => 'CatalogAdmin\View\Helper\SupplyCart',
                'adminCartList'         => 'CatalogAdmin\View\Helper\CartList',
                'AdminOrderDelivery'    => 'CatalogAdmin\View\Helper\OrderDelivery',
                'ProductItem'           => 'Catalog\View\Helper\ProductItem',
                'ProductsList'          => 'Catalog\View\Helper\ProductsList',
                'CatalogMenu'           => 'Catalog\View\Helper\CatalogMenu',
                'CartList'              => 'Catalog\View\Helper\CartList',
                'OrderInfo'             => 'Catalog\View\Helper\OrderInfo',
                'OrdersList'            => 'Catalog\View\Helper\OrdersList',
                'OrderCartList'         => 'Catalog\View\Helper\OrderCartList',
                'ProductsShortList'     => 'Catalog\View\Helper\ProductsShortList',
                'ProductTabs'           => 'Catalog\View\Helper\ProductTabs',
                'MobileProductsShortList' => 'Catalog\View\Helper\MobileProductsShortList',
                'MobileProductsList'    => 'Catalog\View\Helper\MobileProductsList',
                'MobileProductTabs'     => 'Catalog\View\Helper\MobileProductTabs',
                'CartTypeBoxSelect'     => 'Catalog\View\Helper\CartTypeBoxSelect',
            ),
            'factories' => array(
                'cartWidget' => function ($sm) {
                    $catalog = $sm->getServiceLocator()->get('Catalog\Service\CartService')->getCartInfo();
                    return new \Catalog\View\Helper\cartWidget($catalog);
                },
                'catalogWidget' => function ($sm) {
                    $catalog = $sm->getServiceLocator()->get('Catalog\Model\Catalog')->getCollection()->setParentId(0);
                    return new \Catalog\View\Helper\CatalogWidget($catalog);
                },
            )
        );
    }

    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'Catalog\Service\CatalogService'  => 'Catalog\Service\CatalogService',
                'Catalog\Service\ProductsService' => 'Catalog\Service\ProductsService',
                'Catalog\Service\CartService'     => 'Catalog\Service\CartService',
                'Catalog\Service\OrdersService'   => 'Catalog\Service\OrdersService',
                'Catalog\Service\SuppliesService' => 'Catalog\Service\SuppliesService',
                'CatalogAdmin\Model\Orders'       => 'CatalogAdmin\Model\Orders',
                'CatalogAdmin\Model\Products'     => 'CatalogAdmin\Model\Products',
                'CatalogAdmin\Model\Size'         => 'CatalogAdmin\Model\Size',
                'CatalogAdmin\Model\Taste'        => 'CatalogAdmin\Model\Taste',
                'CatalogAdmin\Model\Catalog'      => 'CatalogAdmin\Model\Catalog',
            ),
            'initializers' => array(
                function ($instance, $sm) {
                    if ($instance instanceof \Zend\ServiceManager\ServiceLocatorAwareInterface) {
                        $instance->setServiceLocator($sm);
                    }
                }
            ),
        );
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__           => __DIR__ . '/src/' . __NAMESPACE__,
                    __NAMESPACE__ . 'Admin' => __DIR__ . '/src/Admin',
                )
            )
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}