<?php
namespace Search\Model;

use Aptero\Db\Entity\Entity;

use Zend\Session\Container as SessionContainer;

use \Zend\Crypt\Password\Bcrypt;

class Search extends Entity
{

    public function __construct()
    {
        $this->setTable('search');

        $this->addProperties(array(
            'name'    => array(),
        ));
    }
}