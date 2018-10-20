<?php
namespace ContactsAdmin\Model;

use Aptero\Db\Entity\Entity;

use Zend\Session\Container as SessionContainer;

use \Zend\Crypt\Password\Bcrypt;

class Feedback extends Entity
{
    public function __construct()
    {
        $this->setTable('feedback');

        $this->addProperties(array(
            'name'        => array(),
            'user_id'     => array(),
            'phone'       => array(),
            'question'    => array(),
            'time_create' => array(),
        ));
    }
}