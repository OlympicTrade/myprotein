<?php

namespace User\Service;

use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session;
use Zend\Authentication\Adapter;
use User\Model\Auth\AuthAdapter;

use User\Model\User;


class AuthService extends AuthenticationService
{
    const SESSION_NAME = 'user';

    static protected $user = null;

    /**
     * @var AuthAdapter
     */
    protected $adapter;

    public function __construct()
    {
        $storage = new Session(self::SESSION_NAME);

        $authAdapter = new AuthAdapter();

        parent::__construct($storage, $authAdapter);
    }

    public function setCredentials($username = null, $password = null)
    {
        $this->adapter->setCredentials($username, $password);
    }

    public function getIdentity()
    {
        if(self::$user) {
            return self::$user;
        }

        $storage = $this->getStorage();

        if ($storage->isEmpty() || !($userId = (int) $storage->read())) {
            return null;
        }

        self::$user = new User();
        self::$user->setId($userId)->load();

        if(self::$user->isLoaded()) {
            $online = new \DateTime();
            $online->modify('+3 minute');
            self::$user->set('online', $online->format('Y-m-d H:i:s'))->save();
        }

        return self::$user;
    }

    public function authenticate(Adapter\AdapterInterface $adapter = null)
    {
        $result = $this->adapter->authenticate();

        if ($this->hasIdentity()) {
            $this->clearIdentity();
        }

        if ($result->isValid()) {
            $this->getStorage()->write($result->getIdentity()->getId());
        }

        return $result;
    }

    public function socAuthenticate($data)
    {
        $result = $this->adapter->secAuthenticate($data);

        if ($this->hasIdentity()) {
            $this->clearIdentity();
        }

        if ($result->isValid()) {
            $this->getStorage()->write($result->getIdentity()->getId());
        }
        
        return $result;
    }

    public function clearIdentity()
    {
        $this->getStorage()->clear();

        self::$user = null;
    }

    static public function hasUser()
    {
        $authService = new AuthService();
        return $authService->hasIdentity();
    }

    static public function getUser()
    {
        if(self::$user) {
            return self::$user;
        }

        $authService = new AuthService();
        return $authService->getIdentity();
    }
}
