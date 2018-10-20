<?php

namespace News;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\MvcEvent;

class Module implements AutoloaderProviderInterface
{
    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'News\Service\NewsService' => 'News\Service\NewsService',
                'NewsAdmin\Model\News'  => 'NewsAdmin\Model\News',
            ),
            'initializers' => array(
                function ($instance, $sm) {
                    if ($instance instanceof \Zend\ServiceManager\ServiceLocatorAwareInterface) {
                        $instance->setServiceLocator($sm);
                    }
                }
            ),
            'factories' => array(
                'NewsAdmin\Service\NewsService' => function ($sm) {
                    $service = new \NewsAdmin\Service\NewsService();
                    $service->setModel($sm->get('NewsAdmin\Model\News'));
                    return $service;
                },
            )
        );
    }

    public function getViewHelperConfig() {
        return array(
            'invokables' => array(
                'NewsList'              => 'News\View\Helper\NewsList',
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