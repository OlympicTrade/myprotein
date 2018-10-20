<?php

namespace Contacts;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\MvcEvent;

class Module implements AutoloaderProviderInterface
{
    public function getViewHelperConfig() {
        return array(
            'factories' => array(
                'contactsFooter' => function ($sm) {
                    $contacts = $sm->getServiceLocator()->get('Contacts\Model\Contacts');
                    return new \Contacts\View\Helper\ContactsFooter($contacts);
                },
            )
        );
    }

    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'Contacts\Model\FeedbackService' => 'Contacts\Model\FeedbackService',
            ),
            'initializers' => array(
                function ($instance, $sm) {
                    if ($instance instanceof \Zend\Db\Adapter\AdapterAwareInterface) {
                        $instance->setDbAdapter($sm->get('Zend\Db\Adapter\Adapter'));
                    }
                }
            ),
            'factories' => array(
                'Contacts\Model\Contacts' => function ($sm) {
                    $contacts = new \Contacts\Model\Contacts();
                    return $contacts->setId(1);
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