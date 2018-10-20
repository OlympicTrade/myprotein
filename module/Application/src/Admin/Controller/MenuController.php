<?php
namespace ApplicationAdmin\Controller;

use ApplicationAdmin\Model\MenuItems;
use Aptero\Db\Entity\EntityFactory;
use Aptero\Mvc\Controller\Admin\AbstractActionController;
use Aptero\Service\Admin\TableService;
use ApplicationAdmin\Model\Menu;

class MenuController extends AbstractActionController
{
    public function __construct() {
        parent::__construct();

        $this->setFields(array(
            'name' => array(
                'name'      => 'Название',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'name',
                'width'     => '70',
                'hierarchy' => true,
            ),
            'position' => array(
                'name'      => 'Позиция',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'position',
                'filter'    => function($value) {
                    $positions = Menu::getPositions();
                    return $positions[$value];
                },
                'width'     => '30',
            )
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

        $menuItemsFields = array(
            'name' => array(
                'name'      => 'Название',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'name',
                'width'     => '75',
                'hierarchy' => true,
                'sort'      => array('enabled' =>false)
            ),
            'active' => array(
                'name'      => 'Активен',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'active',
                'filter'    => function($value){
                    return '<i class="far ' . ($value ? 'fa-eye' : 'fa-eye-slash') . '"></i>';
                },
                'width'     => '15',
                'tdStyle'   => array(
                    'text-align' => 'center'
                ),
                'thStyle'   => array(
                    'text-align' => 'center'
                ),
                'sort'      => array('enabled' =>false)
            ),
            'buttons'   => 'asdasd'
        );

        if($model->getId()) {
            $menuItems = EntityFactory::collection(new MenuItems());
            $menuItems
                ->setParentId(0)
                ->select()
                    ->where(array('menu_id' => $model->getId()))
                    ->order('t.sort');

            $view->setVariables(array(
                'menuItems'       => $menuItems,
                'menuItemsFields' => $menuItemsFields,
            ));
        }

        return $view;
    }

    /*
    public function itemEditAction()
    {
        $this->generate();

        $id = $this->params()->fromQuery('id');

        $module = $this->getModule();
        $model = new MenuItems();

        $menuItemEditForm = new MenuItemEditForm();
        $menuItemEditForm->setModel($model);

        if($id && $model->setId($id)->load()) {
            $isUpdate = true;
        } else {
            $isUpdate = false;
            $model->clear();
        }

        if($isUpdate) {
            $this->layout()->getVariable('meta')->title = $module->get('name') . ' - ' . $model->get($this->headerField);
        } else {
            $this->layout()->getVariable('meta')->title = $module->get('name') . ' - Новый пользователь';
        }

        $this->layout()->setVariable('header', $module->get('name'));

        $menuItemEditForm->setData($model->serializeArray());

        $this->view->setVariables(array(
            'model'    => $model,
            'editForm' => $menuItemEditForm,
            'header'   => $isUpdate ?  $model->get($this->headerField) : 'Новая запись'
        ));

        return $this->view;
    }
    */
}