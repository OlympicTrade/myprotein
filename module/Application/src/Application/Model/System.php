<?php
namespace Application\Model;

use Zend\Session\Container as SessionContainer;

class System
{
    const SESSION_NAME = 'system';

    /**
     * @var SessionContainer
     */
    protected $session;

    protected function getSession()
    {
        if(!$this->session) {
            $this->session = new SessionContainer(self::SESSION_NAME);
        }

        return $this->session;
    }
}