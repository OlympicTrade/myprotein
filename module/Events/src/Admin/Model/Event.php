<?php
namespace EventsAdmin\Model;

use Aptero\Db\Entity\Entity;
use Zend\Session\Container as SessionContainer;

class Event extends Entity
{
    public function __construct()
    {
        $this->setTable('events');

        $this->addProperties(array(
            'user_id'        => array(),
            'key'            => array(),
            'title'          => array(),
            'text'           => array(),
            'type'           => array(),
            'url'            => array(),
            'time_create'    => array(),
        ));
    }
}