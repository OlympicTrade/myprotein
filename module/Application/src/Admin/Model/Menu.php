<?php
namespace ApplicationAdmin\Model;

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
    }

    static public function getPositions()
    {
        return array(
            1   => 'Главное меню',
            2   => 'Подвал - Первая колонка',
            3   => 'Подвал - Вторая колонка',
            4   => 'Подвал - Третья колонка',
        );
    }
}