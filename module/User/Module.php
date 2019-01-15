<?php

namespace User;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\MvcEvent;

class Module implements AutoloaderProviderInterface
{
    public function init($moduleManager)
    {
        $sharedManager = $moduleManager->getEventManager()->getSharedManager();
        $sharedManager->attach('Zend\Mvc\Application', 'dispatch', array($this, 'mvcPreDispatch'), 100);
    }

    public function mvcPreDispatch($mvcEvent)
    {
        $sm = $mvcEvent->getApplication()->getServiceManager();
        $auth = new \User\Event\Auth();
        $auth->setServiceManager($sm);
        $auth->setCacheAdapter($sm->get('DataCache'));

        return $auth->preDispatch($mvcEvent);
    }

    public function getViewHelperConfig()   {
        return array(
            'invokables' => array(
                'UserWidget'       => 'User\View\Helper\UserWidget',
            )
        );
    }

    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'User\Model\AclAdapter'    => 'User\Model\Acl\AclAdapter',
                'User\Model\Acl\AclBuilder'=> 'User\Model\Acl\AclBuilder',
                'User\Service\UserService' => 'User\Service\UserService',
            ),
            'initializers' => array(
                function ($instance, $sm) {
                    if ($instance instanceof \Zend\ServiceManager\ServiceLocatorAwareInterface) {
                        $instance->setServiceLocator($sm);
                    }
                }
            ),
            'factories' => array(
                'UserAdmin\Service\UserService' => function ($sm) {
                    return new \UserAdmin\Service\UserService($sm->get('UserAdmin\Model\User'));
                },
                'UserAdmin\Service\PhonesService' => function ($sm) {
                    return new \UserAdmin\Service\PhonesService($sm->get('UserAdmin\Model\Phone'));
                },
                'User\Model\User' => function ($sm) {
                    $userMdl = new \User\Model\User();

                    $userMdl->getEventManager()->attach('initialize', function ($event) {
                        $event->getTarget()->login();
                        return true;
                    });

                    return $userMdl;
                }
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