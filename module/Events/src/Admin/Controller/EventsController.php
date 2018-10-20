<?php
namespace EventsAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;

use Zend\View\Model\JsonModel;

use Aptero\Service\Admin\TableService;

class EventsController extends AbstractActionController
{
    protected $fields = array(
        'id' => array(
            'name'      => 'ID',
            'type'      => TableService::FIELD_TYPE_TEXT,
            'field'     => 'id',
            'width'     => '8'
        ),
        'name' => array(
            'name'      => 'Название',
            'type'      => TableService::FIELD_TYPE_TEXT,
            'field'     => 'title',
            'width'     => '15',
            'tdStyle'   => array(
                'text-align' => 'left'
            ),
            'thStyle'   => array(
                'text-align' => 'left'
            )
        ),
        'desc' => array(
            'name'      => 'Описание',
            'type'      => TableService::FIELD_TYPE_TEXT,
            'field'     => 'text',
            'width'     => '77',
            'tdStyle'   => array(
                'text-align' => 'left'
            ),
            'thStyle'   => array(
                'text-align' => 'left'
            )
        ),
    );
}