<?php
namespace Reviews\Model;

use Aptero\Db\Entity\Entity;

class Review extends Entity
{
    const STATUS_NEW       = 0;
    const STATUS_VERIFIED  = 1;
    const STATUS_REJECTED  = 2;

    public function __construct()
    {
        $this->setTable('reviews');

        $this->addProperties(array(
            'user_id'       => array(),
            'name'          => array(),
            'review'        => array(),
            'answer'        => array(),
            'status'        => array(),
            'date'          => array(),
        ));
    }
}