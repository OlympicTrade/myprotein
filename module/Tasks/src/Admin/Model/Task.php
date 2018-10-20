<?php
namespace TasksAdmin\Model;

use Aptero\Db\Entity\Entity;
use UserAdmin\Model\User;

class Task extends Entity
{
    static public $status = array(
        0 => '',
        1 => 'Выполнена',
        2 => 'Никакая',
        3 => 'Низкая',
        4 => 'Средняя',
        5 => 'Срочная',
        6 => 'Горит',
    );

    public function __construct()
    {
        $this->setTable('tasks');

        $this->addProperties(array(
            'user_id'     => array(),
            'name'        => array(),
            'status'      => array(),
            'desc'        => array(),
        ));

        $this->addPlugin('user', function($model) {
            $item = new User();
            $item->setId($model->get('user_id'));

            return $item;
        }, array('independent' => true));
    }
}