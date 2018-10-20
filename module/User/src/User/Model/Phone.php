<?php
namespace User\Model;

use Aptero\Db\Entity\Entity;

use Balance\Model\Balance;
use Balance\Model\Wallets;
use Zend\Session\Container as SessionContainer;

use \Zend\Crypt\Password\Bcrypt;

class Phone extends Entity
{
    public function __construct()
    {
        $this->setTable('users_phones');

        $this->addProperties(array(
            'phone'        => [],
            'confirmed'    => [],
        ));

        $this->addPropertyFilterIn('phone', function($model, $value) {
            return preg_replace('~\D~', '', $value);
        });
    }
}