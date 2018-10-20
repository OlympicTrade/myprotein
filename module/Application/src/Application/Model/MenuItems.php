<?php
namespace Application\Model;

use Aptero\Db\Entity\EntityHierarchy;

class MenuItems extends EntityHierarchy
{
    const TYPE_PAGE = 1;
    const TYPE_URL  = 2;

    public function __construct()
    {
        $this->setTable('menu_items');

        $this->addProperties(array(
            'name'     => array(),
            'type'     => array(),
            'active'   => array(),
            'page_id'  => array(),
            'url'      => array(),
            'menu_id'  => array(),
        ));


        $this->addPlugin('page', function($model) {
            $page = new \Application\Model\Page();
            $page->setId($model->get('page_id'));

            return $page;
        });
    }
}