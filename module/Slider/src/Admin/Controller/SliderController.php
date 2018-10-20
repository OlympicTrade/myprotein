<?php
namespace SliderAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;

use Zend\View\Model\JsonModel;

use Aptero\Service\Admin\TableService;

class SliderController extends AbstractActionController
{
    public function __construct() {
        parent::__construct();

        $this->setFields(array(
            'img' => array(
                'name'      => 'Изображение',
                'type'      => TableService::FIELD_TYPE_IMAGE,
                'field'     => 'image',
                'filter'    => function($value, $row){
                    return $row->getPlugin('image')->getImage('a');
                },
                'width'     => '10',
                'sort'      => array(
                    'enabled'   => false
                )
            ),
            'sort' => array(
                'name'      => 'Сортировка',
                'type'      => TableService::FIELD_TYPE_EMAIL,
                'field'     => 'sort',
                'width'     => '90',
                'hierarchy' => true,
                'tdStyle'   => array(
                    'text-align' => 'left'
                ),
                'thStyle'   => array(
                    'text-align' => 'left'
                )
            ),
        ));
    }
}