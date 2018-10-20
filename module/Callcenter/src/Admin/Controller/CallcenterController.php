<?php
namespace CallcenterAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;
use Aptero\Service\Admin\TableService;
use CallcenterAdmin\Model\Call;
use CatalogAdmin\Model\Requests;

class CallcenterController extends AbstractActionController
{
    public function __construct() {
        parent::__construct();

        $classes = [
            0  => 'blue',
            1  => 'green',
            2  => 'red',
        ];

        $this->setFields([
            /*'id' => [
                'name'      => 'ID',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'id',
                'width'     => '6'
            ],*/
            'theme' => [
                'name'      => 'Тема',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'theme',
                'width'     => '17',
            ],
            'phone_id' => array(
                'name'      => 'Телефон',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'login',
                'filter'    => function($value, $row){
                    return $row->getPlugin('phone')->get('phone');
                },
                'width'     => '13',
            ),
            'item_id' => array(
                'name'      => 'Ссылка',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'login',
                'filter'    => function($value, $row){
                    switch($row->get('type_id')) {
                        case \CallcenterAdmin\Model\Call::TYPE_REQUEST:
                            $product = $row->getPlugin('item')->getPlugin('product');
                            return '<a href="' . $product->getEditUrl() . '">' . $product->get('name') . '</a> (' . $product->get('size') . ' - ' . $product->get('taste') . ')';
                            break;
                        case \CallcenterAdmin\Model\Call::TYPE_ORDER:
                            $order = $row->getPlugin('item');
                            return '<a href="' . $order->getEditUrl() . '">Заказ №' . $order->getId() . '</a>';
                            break;
                        case \CallcenterAdmin\Model\Call::TYPE_RETURN:
                            $order = $row->getPlugin('item');
                            return '<a href="' . $order->getEditUrl() . '">Возврат заказа №' . $order->getId() . '</a>';
                            break;
                    }

                    return '';
                },
                'width'     => '30',
            ),

            'status' => [
                'name'      => 'Статус',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'status',
                'width'     => '40',
                'filter'    => function($value, $row) use ($classes){
                    return '<span class="wrap ' . $classes[$value] . '">' . Call::$statuses[$value] . '</span>';
                },
            ],
        ]);
    }
    
    public function completeAction()
    {
        $id = $this->params()->fromPost('id');
        $status = $this->params()->fromPost('status');
        
        $this->getService()->completeCall($id, $status);
        
        die();
    }
}