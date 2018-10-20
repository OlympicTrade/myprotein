<?php
namespace ContactsAdmin\Model;

use Aptero\Db\Entity\Entity;
use Zend\Session\Container as SessionContainer;

class Contacts extends Entity
{
    public function __construct()
    {
        $this->setTable('contacts');

        $this->addProperties(array(
            'email'       => array(),
            'skype'       => array(),
            'vkontakte'   => array(),
            'facebook'    => array(),
            'odnoklassniki'    => array(),
            'youtube'     => array(),
            'address'     => array(),
            'latitude'    => array(),
            'longitude'   => array(),
            'show_map'    => array(),
            'phone_1'     => array(),
            'phone_2'     => array(),
            'phone_3'     => array(),
            'time_update' => array(),
            'sort'        => array(),
        ));
    }
}