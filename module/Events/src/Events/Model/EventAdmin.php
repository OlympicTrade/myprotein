<?php
namespace Events\Model;

use Aptero\Db\Entity\Entity;

class EventAdmin extends Entity
{
    public function __construct()
    {
        $this->setTable('events_admin');

        $this->addProperties(array(
            'key'            => array(),
            'title'          => array(),
            'text'           => array(),
            'type'           => array(),
            'url'            => array(),
            'time_create'    => array(),
        ));
    }
}