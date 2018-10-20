<?php
namespace EventsAdmin\Model;

use Aptero\Db\Entity\Entity;
use Zend\Session\Container as SessionContainer;

class EventsModel extends Entity
{
    public function __construct()
    {
        $this->setTable('events');

        $this->addProperties(array(
            'name'        => array(),
            'time_update' => array(),
            'sort'        => array(),
        ));
    }
}