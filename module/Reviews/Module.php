<?php

namespace Reviews;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;

class Module implements AutoloaderProviderInterface
{
    public function getViewHelperConfig() {
        return array(
            'invokables' => array(
                'ReviewsList'  => 'Reviews\View\Helper\ReviewsList',
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'Reviews\Service\ReviewsService' => 'Reviews\Service\ReviewsService',
            ),
            'initializers' => array(
                function ($instance, $sm) {
                    if ($instance instanceof \Zend\ServiceManager\ServiceLocatorAwareInterface) {
                        $instance->setServiceLocator($sm);
                    }
                }
            ),
            'factories' => array(
                'ReviewsAdmin\Service\ReviewsService' => function ($sm) {
                    $service = new \ReviewsAdmin\Service\ReviewsService();
                    $service->setModel($sm->get('ReviewsAdmin\Model\Review'));
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