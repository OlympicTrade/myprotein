<?php
namespace Contacts\Model;

use Aptero\Db\Entity\Entity;

use Zend\Session\Container as SessionContainer;

use \Zend\Crypt\Password\Bcrypt;

class Contacts extends Entity
{
    public function __construct()
    {
        $this->setTable('contacts')
            ->enableCache()
            ->addProperties([
                'email'       => [],
                'skype'       => [],
                'vkontakte'   => [],
                'facebook'    => [],
                'youtube'     => [],
                'address'     => [],
                'latitude'    => [],
                'longitude'   => [],
                'show_map'    => [],
                'phone_1'     => [],
                'phone_2'     => [],
                'phone_3'     => [],
            ]);
    }
}