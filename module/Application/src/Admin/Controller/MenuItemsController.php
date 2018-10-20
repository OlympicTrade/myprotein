<?php
namespace ApplicationAdmin\Controller;

use ApplicationAdmin\Form\MenuItemEditForm;
use ApplicationAdmin\Model\MenuItems;
use Aptero\Mvc\Controller\Admin\AbstractActionController;
use Aptero\Service\Admin\TableService;
use ApplicationAdmin\Model\Menu;

class MenuItemsController extends AbstractActionController
{
    /**
     * @return TableService
     */
    protected function getService()
    {
        $service = $this->getServiceLocator()->get('ApplicationAdmin\Service\MenuItemsService')
            ->setModuleName('application')
            ->setSectionName('menuItems');

        return $service;
    }

    /*public function editAction()
    {
        $view = parent::editAction();

        $this->view->setVariables(array(
            'model'    => $model,
            'editForm' => $editForm,
            'header'   => $isUpdate ?  $model->get($this->headerField) : 'Новая запись'
        ));



        return $this->view;
    }*/
}