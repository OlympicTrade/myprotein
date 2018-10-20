<?php
namespace Events\Model;

use Aptero\Db\Entity\Entity;

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