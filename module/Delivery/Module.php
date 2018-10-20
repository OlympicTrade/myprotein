<?php

namespace Delivery;

use DeliveryAdmin\Model\Point;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\MvcEvent;

class Module implements AutoloaderProviderInterface
{
    public function getViewHelperConfig() {
        return array(
            'invokables' => array(
                'DeliveryNotice'    => 'Delivery\View\Helper\DeliveryNotice',
                'PickupPoints'      => 'Delivery\View\Helper\PickupPoints',
            ),
        );
    }
    
    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'Delivery\Service\DeliveryService'  => 'Delivery\Service\DeliveryService',
                'Delivery\Service\GlavpunktService' => 'Delivery\Service\GlavpunktService',
                
            ),
            'initializers' => array(
                function ($instance, $sm) {
                    if ($instance instanceof \Zend\ServiceManager\ServiceLocatorAwareInterface) {
                        $instance->setServiceLocator($sm);
                    }
                }
            ),
            'factories' => array(
                'DeliveryAdmin\Service\DeliveryService' => function ($sm) {
                    $service = new \DeliveryAdmin\Service\DeliveryService();
                    $service->setModel($sm->get('DeliveryAdmin\Model\Delivery'));
                    return $service;
                },
                'DeliveryAdmin\Service\PointsService' => function ($sm) {
                    $service = new \DeliveryAdmin\Service\PointsService();
                    $service->setModel(new Point());
                    return $service;
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