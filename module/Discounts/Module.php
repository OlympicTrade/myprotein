<?php

namespace Discounts;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;

class Module implements AutoloaderProviderInterface
{
    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'Discounts\Service\DiscountsService' => 'Discounts\Service\DiscountsService',
            ),
            'initializers' => array(
                function ($instance, $sm) {
                    if ($instance instanceof \Zend\ServiceManager\ServiceLocatorAwareInterface) {
                        $instance->setServiceLocator($sm);
                    }
                }
            ),
            'factories' => array(
                'DiscountsAdmin\Service\DiscountsService' => function ($sm) {
                    return new \DiscountsAdmin\Service\DiscountsService($sm->get('DiscountsAdmin\Model\Discount'));
                },
            )
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