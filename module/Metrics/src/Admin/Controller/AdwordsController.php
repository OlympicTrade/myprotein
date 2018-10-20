<?php
namespace MetricsAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;
use Aptero\Service\Admin\TableService;

class AdwordsController extends AbstractActionController
{
    public function __construct() {
        parent::__construct();

        $this->setFields([
            'source' => [
                'name'      => 'Источник',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'source',
                'width'     => '10',
            ],
            'campaign' => [
                'name'      => 'Компания',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'campaign',
                'width'     => '10',
            ],
            'cost' => [
                'name'      => 'Бюджет',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'cost',
                'width'     => '10',
            ],
            'date' => [
                'name'      => 'Дата',
                'type'      => TableService::FIELD_TYPE_DATE,
                'field'     => 'date',
                'width'     => '15',
            ],
            'clients' => [
                'name'      => 'Переходов',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'filter'    => function($value, $row){
                    return $row->getPlugin('visits')->get('clients');
                },
                'width'     => '10',
                'sort'      => array(
                    'enabled'   => false
                )
            ],
            'sessions' => [
                'name'      => 'Сессии',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'filter'    => function($value, $row){
                    return $row->getPlugin('visits')->get('sessions');
                },
                'width'     => '10',
                'sort'      => array(
                    'enabled'   => false
                )
            ],
            'views' => [
                'name'      => 'Просмотров',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'filter'    => function($value, $row){
                    return $row->getPlugin('visits')->get('views');
                },
                'width'     => '10',
                'sort'      => array(
                    'enabled'   => false
                )
            ],
            'cross' => [
                'name'      => 'Повторов',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'cross',
                'width'     => '25',
            ],
            /*'spot' => array(
                'name'      => 'Фото',
                'type'      => TableService::FIELD_TYPE_IMAGE,
                'field'     => 'image',
                'filter'    => function($value, $row){
                    return $row->getPlugin('image')->getImage('a');
                },
                'width'     => '10',
                'sort'      => array(
                    'enabled'   => false
                )
            ),*/
        ]);
    }
}