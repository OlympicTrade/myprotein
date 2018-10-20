<?php
namespace CatalogAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;

use CatalogAdmin\Model\Requests;

use Aptero\Service\Admin\TableService;

class RequestsController extends AbstractActionController
{
    public function __construct() {
        parent::__construct();

        $classes = [
            0  => 'gray',
            1  => 'green',
            2  => 'red',
        ];

        $this->setFields([
            'product_id' => [
                'name'      => 'Товар',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'image',
                'filter'    => function($value, $row){
                    return $row->getPlugin('product')->get('name');
                },
                'width'     => '20',
            ],
            'contact' => [
                'name'      => 'Контакты',
                'type'      => TableService::FIELD_TYPE_LINK,
                'field'     => 'contact',
                'width'     => '15',
            ],
            'status' => [
                'name'      => 'Статус',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'status',
                'width'     => '65',
                'filter'    => function($value, $row) use ($classes){
                    return '<span class="wrap ' . $classes[$value] . '">' . Requests::$statuses[$value] . '</span>';
                },
            ],
        ]);
    }
}