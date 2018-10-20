<?php
namespace ContactsAdmin\Model;

use Aptero\Db\Entity\Entity;
use Aptero\Db\Entity\EntityHierarchy;

class Subscribe extends EntityHierarchy
{
    public function __construct()
    {
        $this->setTable('subscribe');

        $this->addProperties(array(
            'email'      => array(),
        ));
    }
}