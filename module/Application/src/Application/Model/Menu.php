<?php
namespace Application\Model;

use Aptero\Db\Entity\Entity;

class Menu extends Entity
{
    const POSITION_TOP    = 1;
    const POSITION_BOTTOM = 2;

    public function __construct()
    {
        $this->setTable('menu');

        $this->addProperties(array(
            'name'      => array(),
            'position'  => array(),
        ));

        $this->addPlugin('items', function($model) {
            $items = new \Application\Model\MenuItems();
            $items->select()->where(array('menu_id' => $model->getId()));

            return $items->getCollection();
        });
    }

    static public function getPositions()
    {
        return array(
            self::POSITION_TOP      => 'Шапка',
            self::POSITION_BOTTOM   => 'Подвал',
        );
    }
}