<?php

namespace Events;

use Aptero\Db\Entity\Entity;
use Deals\Service\DealsService;
use Events\Model\Event;
use EventsAdmin\Model\Event as EventAdmin;
use User\Service\AuthService;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\MvcEvent;

class Module implements AutoloaderProviderInterface
{


    public function getViewHelperConfig() {
        return array(
            'invokables' => array(
                'EventsList'       => 'Events\View\Helper\EventsList',
            ),
            'factories' => array(
                'EventsShortList' => function($sm) {
                    $sm = $sm->getServiceLocator();
                    $events = $sm->get('Events\Service\EventsService')->getEventsList(1, 10);
                    return new \Events\View\Helper\ShortList($events);
                },
                'AdminEventsShortList' => function($sm) {
                    $sm = $sm->getServiceLocator();
                    $events = $sm->get('EventsAdmin\Service\EventsService')->getEventsList(1);
                    return new \EventsAdmin\View\Helper\ShortList($events);
                }
            )
        );
    }

    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'Events\Service\EventsService' => 'Events\Service\EventsService',
            ),
            'initializers' => array(
                function ($instance, $sm) {
                    if ($instance instanceof \Zend\ServiceManager\ServiceLocatorAwareInterface) {
                        $instance->setServiceLocator($sm);
                    }
                }
            ),
            'factories' => array(
                'EventsAdmin\Service\EventsService' => function ($sm) {
                    $service = new \EventsAdmin\Service\EventsService();
                    $service->setModel($sm->get('EventsAdmin\Model\Event'));
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