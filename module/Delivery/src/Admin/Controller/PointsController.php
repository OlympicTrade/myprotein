<?php
namespace DeliveryAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;
use Aptero\Service\Admin\TableService;
use DeliveryAdmin\Model\Delivery;

class PointsController extends AbstractActionController
{
    public function __construct() {
        parent::__construct();

        $this->setFields([
            'address' => [
                'name'      => 'Адрес',
                'type'      => TableService::FIELD_TYPE_LINK,
                'width'     => '40',
            ],
            'city' => [
                'name'      => 'Город',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'width'     => '14',
            ],
            'phone' => [
                'name'      => 'Телефон',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'width'     => '46',
            ],
        ]);
    }
}