<?php
namespace BalanceAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;
use Aptero\Service\Admin\TableService;

class BalanceController extends AbstractActionController
{
    public function __construct() {
        parent::__construct();

        $this->setFields(array(
            'name' => array(
                'name'      => 'Название',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'name',
                'width'     => '30',
            ),
            'income' => array(
                'name'      => 'Доход',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'income',
                'width'     => '20',
            ),
            'outgo' => array(
                'name'      => 'Расход',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'outgo',
                'width'     => '20',
            ),
            'date' => array(
                'name'      => 'Дата',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'date',
                'width'     => '30',
            ),
        ));
    }
}