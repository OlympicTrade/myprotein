<?php
namespace TasksAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;
use Aptero\Service\Admin\TableService;
use TasksAdmin\Model\Task;

class TasksController extends AbstractActionController
{
    public function __construct()
    {
        $classes = array(
            1 => 'gray',
            2 => 'blue',
            3 => 'green',
            4 => 'yellow',
            5 => 'orange',
            6 => 'red',
        );

        parent::__construct();

        $this->setFields(array(
            'name' => array(
                'name'      => 'Задача',
                'type'      => TableService::FIELD_TYPE_LINK,
                'field'     => 'name',
                'width'     => '50',
            ),
            'user_id' => array(
                'name'      => 'Исполнитель',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'user_id',
                'filter'    => function($value, $row){
                    return $row->getPlugin('user')->get('name');
                },
                'width'     => '25',
            ),
            'status' => array(
                'name'      => 'Срочность',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'status',
                'filter'    => function($value, $row) use ($classes){
                    return '<span class="wrap ' . $classes[$row->get('status')] . '">' . Task::$status[$row->get('status')] . '</span>';
                },
                'width'     => '25',
            ),
        ));
    }
}