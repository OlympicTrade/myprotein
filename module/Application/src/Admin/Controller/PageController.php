<?php
namespace ApplicationAdmin\Controller;

use ApplicationAdmin\Model\Content;
use Aptero\Mvc\Controller\Admin\AbstractActionController;

use Aptero\Service\Admin\TableService;

class PageController extends AbstractActionController
{
    public function __construct() {
        parent::__construct();

        $this->setFields(array(
            'name' => array(
                'name'      => 'Название',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'name',
                'width'     => '58',
                'hierarchy' => true,
            ),
            'url' => array(
                'name'      => 'URL',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'url',
                'width'     => '14',
            ),
            'active' => array(
                'name'      => 'Активна',
                'type'      => TableService::FIELD_TYPE_BOOL,
                'field'     => 'active',
                'filter'    => function($value){
                    return '<i class="far ' . ($value ? 'fa-eye' : 'fa-eye-slash') . '"></i>';
                },
                'width'     => '14',
                'tdStyle'   => array(
                    'text-align' => 'center'
                ),
                'thStyle'   => array(
                    'text-align' => 'center'
                )
            ),
            'module' => array(
                'name'      => 'Модуль',
                'type'      => TableService::FIELD_TYPE_BOOL,
                'field'     => 'module_id',
                'filter'    => function($value){
                    return ($value ? '<i class="fas fa-cog"></i>' : '');
                },
                'width'     => '14',
                'tdStyle'   => array(
                    'text-align' => 'center'
                ),
                'thStyle'   => array(
                    'text-align' => 'center'
                )
            ),
        ));
    }

    public function editAction()
    {
        $view = parent::editAction();
        $id = $this->params()->fromQuery('id');

        $model = $view->getVariable('model');
        if(!$model) {
            $model = $this->getService()->getModel();
            $model->setId($id);
        }

        $this->viewHelper('headScript')->appendFile('/admin/js/page-list.js');

        $contentItemsFields = array(
            'name' => array(
                'name'      => 'Название',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'title',
                'width'     => '60',
                'hierarchy' => true,
            ),
            'sort' => array(
                'name'      => 'Сортировка',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'sort',
                'width'     => '40',
            ),
        );

        if($model->getId()) {
            $contentItems = Content::getEntityCollection();
            $contentItems->select()
                ->where(array('depend' => $model->getId()))
                ->order('t.sort');

            $view->setVariables(array(
                'contentItems'       => $contentItems,
                'contentItemsFields' => $contentItemsFields,
            ));
        }

        return $view;
    }
}