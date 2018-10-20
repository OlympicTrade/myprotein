<?php
namespace TestsAdmin\Model;

use Aptero\Db\Entity\Entity;
use Zend\Session\Container as SessionContainer;

class Test extends Entity
{
    public function __construct()
    {
        $this->setTable('tests');

        $this->addProperties(array(
            'name'        => array(),
            'time_update' => array(),
            'sort'        => array(),
        ));
    }
}