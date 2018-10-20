<?php

namespace User\Model\Auth;

use Zend\Authentication\Result;

class AuthUser extends Result
{
    public function __construct($code, $identity, array $messages = array())
    {
        $this->code     = (int) $code;
        $this->identity = $identity;
        $this->messages = $messages;
    }

    public function isValid()
    {
        return ($this->code > 0) ? true : false;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getIdentity()
    {
        return $this->identity;
    }

    public function getMessages()
    {
        return $this->messages;
    }
}
