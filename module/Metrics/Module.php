<?php

namespace Metrics;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\MvcEvent;

class Module implements AutoloaderProviderInterface
{
    public function getViewHelperConfig() {
        return array(
            'invokables' => array(
                'adminPeriodsMetrics' => 'MetricsAdmin\View\Helper\PeriodsMetrics',
            ),
        );
    }
    
    public function getServiceConfig()
    {
        return [
            'initializers' => [
                function ($instance, $sm) {
                    if ($instance instanceof \Zend\ServiceManager\ServiceLocatorAwareInterface) {
                        $instance->setServiceLocator($sm);
                    }
                }
            ],
            'invokables' => array(
                'MetricsAdmin\Service\MetricsService' => 'MetricsAdmin\Service\MetricsService',
                'Metrics\Service\MetricsService'      => 'Metrics\Service\MetricsService',
            ),
            'factories' => [
                'MetricsAdmin\Service\AdwordsService' => function ($sm) {
                    return new \MetricsAdmin\Service\AdwordsService(new \MetricsAdmin\Model\Adwords());
                },
                'MetricsAdmin\Service\CashService' => function ($sm) {
                    return new \MetricsAdmin\Service\CashService(new \MetricsAdmin\Model\Cash());
                },
            ]
        ];
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__           => __DIR__ . '/src/' . __NAMESPACE__,
                    __NAMESPACE__ . 'Admin' => __DIR__ . '/src/Admin',
                ]
            ]
        ];
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}