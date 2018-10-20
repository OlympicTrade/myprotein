<?php

namespace User\Event;

use Zend\Mvc\MvcEvent;
use User\Model\Acl\AclBuilder;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Cache\Storage\Adapter\AbstractAdapter as CacheAdapter;
use Aptero\Cache\CacheAwareInterface;

use User\Service\AuthService;
use User\Model\User;

class Auth implements ServiceManagerAwareInterface, CacheAwareInterface
{
    protected $authPlugin = null;

    /**
     * @var AclBuilder
     */
    protected $acl = null;

    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;

    /**
     * @var CacheAdapter
     */
    protected $cache = null;

    public function preDispatch(MvcEvent $event)
    {
        $acl = $this->getAcl();

        $user = $this->getServiceManager()->get('User\Model\User');

        $role = $user->getRole();

        $routeMatch = $event->getRouteMatch();

        $resource = $this->getResourceName($routeMatch);

        $authService = new AuthService();
        if($user = $authService->getIdentity()) {
            $role = $user->get('type');
        }

        /*
        var_dump($role);
        var_dump($resource);
        var_dump($acl->isAllowed($role, $resource));
        die();
        */

        if (!$acl->isAllowed($role, $resource)) {
            $side = $routeMatch->getParam('side');

            if($side == 'admin') {
                if($role != User::ROLE_ADMIN) {
                    $url = $event->getRouter()->assemble(array('action' => 'login'), array('name' => 'adminUser'));
                } else {
                    $url = $event->getRouter()->assemble(array('action' => 'index'), array('name' => 'admin'));
                }
            } else {
                if($role == User::ROLE_GUEST) {
                    $url = $event->getRouter()->assemble(array('action' => 'login'), array('name' => 'user'));
                } else {
                    $url = $event->getRouter()->assemble(array('action' => 'index'), array('name' => 'user'));
                }
            }

            $response = $event->getResponse();
            $response->getHeaders()->addHeaderLine('Location', $url);
            $response->setStatusCode(302);
            $response->sendHeaders();
        }
    }

    protected function getResourceName(\Zend\Mvc\Router\Http\RouteMatch $routeMatch)
    {
        $controller = strtolower($routeMatch->getParam('controller'));

        $temp = explode('\\', $controller);

        /*$module     = $temp[0];
        $controller = $temp[2];*/
        /*$module     = strtolower($routeMatch->getParam('module'));
        if(!$module) {
            $module = strtolower($temp[0]);
        }*/

        $module = strtolower($temp[0]);
        $controller = $temp[2];
        $action     = $routeMatch->getParam('action');

        $resource = rtrim($module . ':' . $controller . ':' . $action);

        if(!$this->acl->hasResource($resource)) {
            $resource = $module . ':' . $controller;
            if(!$this->acl->hasResource($resource)) {
                $resource = $module;
                if(!$this->acl->hasResource($resource)) {
                    throw new \Exception('Resource ' . $resource . ' not found');
                }
            }
        }

        return $resource;
    }

    public function getAcl()
    {
        if (!$this->acl) {
            $cacheName = 'acl';

            if(!$this->acl = $this->cache->getItem($cacheName)) {
                $this->acl = $this->getServiceManager()->get('User\Model\Acl\AclBuilder')->initialize($this->getServiceManager());

                $this->cache->setItem($cacheName, $this->acl);
            }
        }

        return $this->acl;
    }

    public function setCacheAdapter(CacheAdapter $cache)
    {
        $this->cache = $cache;
    }

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function getServiceManager()
    {
        return $this->serviceManager;
    }
}
