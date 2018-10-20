<?php
namespace ReviewsAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;

use ReviewsAdmin\Model\Review;
use Zend\View\Model\JsonModel;

use Aptero\Service\Admin\TableService;

class ReviewsController extends AbstractActionController
{
    public function __construct() {
        parent::__construct();

        $this->setFields(array(
            'name' => array(
                'name'      => 'Имя',
                'type'      => TableService::FIELD_TYPE_LINK,
                'field'     => 'name',
                'width'     => '10',
            ),
            'review' => array(
                'name'      => 'Имя',
                'type'      => TableService::FIELD_TYPE_LINK,
                'field'     => 'review',
                'width'     => '66',
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
                'width'     => '14',
            ),
            'status' => array(
                'name'      => 'Статус',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'status',
                'filter'    => function($value){
                    return '<i class="fas ' . ($value == Review::STATUS_VERIFIED ? 'fa-eye' : 'fa-eye-slash') . '"></i>';
                },
                'width'     => '10',
                'tdStyle'   => array(
                    'text-align' => 'center'
                ),
                'thStyle'   => array(
                    'text-align' => 'center'
                )
            ),
        ));
    }
}