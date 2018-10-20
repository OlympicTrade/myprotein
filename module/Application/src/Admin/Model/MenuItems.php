<?php
namespace ApplicationAdmin\Model;

use Aptero\Db\Entity\EntityHierarchy;

class MenuItems extends EntityHierarchy
{
    public function __construct()
    {
        $this->setTable('menu_items');

        $this->addProperties(array(
            'name'     => array(),
            'parent'   => array(),
            'type'     => array('default' => 1),
            'active'   => array('default' => 1),
            'page_id'  => array(),
            'url'      => array(),
            'menu_id'  => array(),
            'sort'     => array(),
        ));
    }
}