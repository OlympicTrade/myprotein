<?php
namespace BlogAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;
use Aptero\Service\Admin\TableService;
use BlogAdmin\Model\Comment;

class CommentsController extends AbstractActionController
{
    public function __construct() {
        parent::__construct();

        $classes = array(
            0  => 'red',
            1  => 'green',
            2  => 'gray',
        );

        $this->setFields(array(
            'name' => array(
                'name'      => 'Имя',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'name',
                'width'     => '14',
                'sort'      => array(
                    'enabled'   => false
                ),
            ),
            'article_id' => array(
                'name'      => 'Статья',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'product_id',
                'filter'    => function($value, $row, $view){
                    $product = $row->getPlugin('article');
                    $url = $view->url('adminBlog', array('action' => 'edit')) . '?id=' . $product->get('id');

                    return '<a href="' . $url . '">' . $product->get('name') . '</a>';
                },
                'width'     => '32',
            ),
            'user_id' => array(
                'name'      => 'Пользователь',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'user_id',
                'filter'    => function($value, $row, $view){
                    $user = $row->getPlugin('user');
                    $url = $view->url('adminUser', array('action' => 'edit')) . '?id=' . $user->get('id');

                    return '<a href="' . $url . '">' . $user->get('email') . '</a>';
                },
                'width'     => '18',
            ),
            'status' => array(
                'name'      => 'Статус',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'status',
                'width'     => '18',
                'filter'    => function($value, $row) use ($classes){
                    return '<span class="wrap ' . $classes[$value] . '">' . Comment::$statuses[$value]. '</span>';
                },
            ),
            'time_create' => array(
                'name'      => 'Дата заказа',
                'type'      => TableService::FIELD_TYPE_DATE,
                'field'     => 'time_create',
                'width'     => '18',
            )));
    }
}