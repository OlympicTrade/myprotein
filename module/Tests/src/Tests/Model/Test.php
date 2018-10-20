<?php
namespace Tests\Model;

use Aptero\Db\Entity\Entity;

class Test extends Entity
{
    public function __construct()
    {
        $this->setTable('tests');

        $this->addProperties(array(
            'name'          => array(),
            'time_create'   => array(),
        ));
    }
}