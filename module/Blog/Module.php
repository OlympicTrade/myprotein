<?php

namespace Blog;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\MvcEvent;

class Module implements AutoloaderProviderInterface
{
    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'Blog\Service\BlogService' => 'Blog\Service\BlogService',
                'BlogAdmin\Model\Article'  => 'BlogAdmin\Model\Article',
            ),
            'initializers' => array(
                function ($instance, $sm) {
                    if ($instance instanceof \Zend\ServiceManager\ServiceLocatorAwareInterface) {
                        $instance->setServiceLocator($sm);
                    }
                }
            ),
            'factories' => array(
                'BlogAdmin\Service\BlogService' => function ($sm) {
                    $service = new \BlogAdmin\Service\BlogService();
                    $service->setModel($sm->get('BlogAdmin\Model\Article'));
                    return $service;
                },
                'BlogAdmin\Service\CommentsService' => function ($sm) {
                    $service = new \BlogAdmin\Service\CommentsService();
                    $service->setModel($sm->get('BlogAdmin\Model\Comment'));
                    return $service;
                },
            )
        );
    }

    public function getViewHelperConfig() {
        return array(
            'invokables' => array(
                'ArticlesReco'         => 'Blog\View\Helper\ArticlesReco',
                'ArticlesList'         => 'Blog\View\Helper\ArticlesList',
                'ArticlesComments'     => 'Blog\View\Helper\ArticlesComments',
            ),
            'initializers' => array(
                function ($instance, $helperPluginManager) {
                    $sm = $helperPluginManager->getServiceLocator();

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