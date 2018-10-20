<?php
namespace NewsAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;

use Zend\View\Model\JsonModel;

use Aptero\Service\Admin\TableService;

class NewsController extends AbstractActionController
{
    protected $fields = array(
        'name' => array(
            'name'      => 'Название',
            'type'      => TableService::FIELD_TYPE_TEXT,
            'field'     => 'name',
            'width'     => '25',
            'tdStyle'   => array(
                'text-align' => 'left'
            ),
            'thStyle'   => array(
                'text-align' => 'left'
            )
        ),
        'text' => array(
            'name'      => 'Текст',
            'type'      => TableService::FIELD_TYPE_TEXT,
            'field'     => 'text',
            'width'     => '75',
            'tdStyle'   => array(
                'text-align' => 'left'
            ),
            'thStyle'   => array(
                'text-align' => 'left'
            )
        )
    );
}