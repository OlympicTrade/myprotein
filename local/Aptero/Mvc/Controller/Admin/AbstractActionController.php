<?php

namespace Aptero\Mvc\Controller\Admin;

use Aptero\Yandex\Client;
use Zend\Mvc\Controller\AbstractActionController as ZendActionController;
use Aptero\Service\Admin\TableService;

use Zend\Session\Container;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

abstract class AbstractActionController extends ZendActionController
{
    /**
     * @var ViewModel
     */
    protected $view = null;

    /**
     * @var array
     */
    protected $fields = array(
        'name' => array(
            'name'      => 'Название',
            'type'      => TableService::FIELD_TYPE_LINK,
            'field'     => 'name',
            'width'     => '100',
            'tdStyle'   => array(
                'text-align' => 'left'
            ),
            'thStyle'   => array(
                'text-align' => 'left'
            )
        ),
    );

    /**
     * @var string
     */
    protected $headerField = 'name';

    public function __construct()
    {
        /*Client::getInstance()->auth(['redirect' => '/admin/']);

        $session = new Container();
        if($session->redirect) {
            $redirect = $session->redirect;
            $session->redirect = null;
            return $this->redirect()->toUrl($redirect);
        }*/

        $this->getEventManager()->attach('dispatch', array($this, 'init'), 100);
    }

    public function init()
    {
        $this->layout('layout/admin/child');
        $this->view = new ViewModel();
    }

    public function updateTableCellAction()
    {
        $id = $this->params()->fromPost('id');
        $field = $this->params()->fromPost('field');
        $value = $this->params()->fromPost('value');

        $model = $this->getService()->getModel();
        $model->setId($id)->load();
        $model->set($field, $value)->save();

        return new JsonModel([]);
    }

    public function indexAction()
    {
        $module = $this->getServiceLocator()->get('Application\Model\Module')->load();

        return $this->redirect()->toRoute('admin', array('module' => $module->get('module'), 'section' => $module->get('section'), 'action' => 'list'));
    }

    public function listAction()
    {
        $this->generate();

        $module = $module = $this->getModule();
        $this->layout()->setVariable('header', $module->get('name'));

        $modelService = $this->getService();

        $lines = (int) $this->params()->fromQuery('rows', 20);
        $page = (int) $this->params()->fromQuery('page', 1);
        $sort = $this->params()->fromQuery('sort', '');
        $direct = $this->params()->fromQuery('direct', '');

        $filterForm = $modelService->getFilterForm($module);
        $filterForm->setData($this->params()->fromQuery());
        $filterForm->setFilters();

        if(!$filterForm->isValid()) {
            var_dump($filterForm->getMessages());
            die();
        }

        $modelPaginator = $modelService->getList($sort, $direct, $filterForm->getData(), 0);

        $modelPaginator->setCurrentPageNumber($page);
        $modelPaginator->setItemCountPerPage($lines);

        $this->view->setVariables(array(
            'tableData' => $modelPaginator,
            'fields'    => $this->fields,
            'filterForm'=> $filterForm
        ));

        $this->layout()->getVariable('meta')->title = $module->get('name') . ' - cписок';

        $this->viewHelper('headScript')->appendFile('/engine/js/page-list.js');

        return $this->view;
    }

    public function editAction()
    {
        $this->generate();
        $request = $this->getRequest();

        $id = $this->params()->fromQuery('id');

        $module = $this->getModule();
        $modelService = $this->getService();
        $model = $modelService->getModel();

        $editForm = $modelService->getEditForm();
        if($id && $model->setId($id)->load()) {
            $isUpdate = true;
        } else {
            $isUpdate = false;
        }

        $editForm->setModel($model);
        $editForm->setData($model->serializeArray());

        if($isUpdate) {
            $this->layout()->getVariable('meta')->title = $module->get('name') . ' - ' . $model->get($this->headerField);
        } else {
            $this->layout()->getVariable('meta')->title = $module->get('name') . ' - Новый пользователь';
        }
        $this->layout()->setVariable('header', $module->get('name'));

        if ($request->isPost()) {
            $editForm->setFilters();
            $editForm->setData(array_replace($model->serializeArray(), $this->params()->fromPost()));

            if($request->isXmlHttpRequest()) {
                if($editForm->isValid()) {
                    if($this->params()->fromPost('requestType') == 'submit') {
                        if(!$isUpdate) {
                            $this->flashMessenger()->addMessage(array('text' => 'Запись сохранена', 'type' => 'success'));
                        }

                        $modelService->save($editForm->getData(), $model);
                        $resp = array('update' => (int) $isUpdate, 'id' => $model->getId());
                    } else {
                        $resp = array();
                    }
                } else {
                    $resp['errors'] = $editForm->getMessages();
                }

                $jsonModel = new JsonModel($resp);
                return $jsonModel;
            }
        }

        $editForm->setData($model->serializeArray());

        $this->view->setVariables(array(
            'model'    => $model,
            'editForm' => $editForm,
            'header'   => $isUpdate ?  $model->get($this->headerField) : 'Новая запись'
        ));

        $this->viewHelper('headScript')
            ->appendFile('/engine/js/page-edit.js')
            ->appendFile('/engine/js/ckfinder/ckfinder.js')
            ->appendFile('/engine/js/jquery/spellchecker.js')
            ->appendFile('/engine/js/ckeditor/ckeditor.js')
            ->appendFile('/engine/js/jquery/form-validator.js')
            ->appendFile('/engine/js/form.js');

        $this->viewHelper('headLink')
            ->prependStylesheet('/engine/css/jquery/spellchecker.css');

        return $this->view;
    }

    public function settingsAction()
    {
        $this->generate();

        $module = $this->getModule();
        $header = $module->get('name') . ' - настройки';
        $this->layout()->getVariable('meta')->title = $module->get('name') . ' - Новый пользователь';
        $modelService = $this->getService();

        $settingsForm = $modelService->getSettingsForm();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $settingsForm->setFilters();
            $settingsForm->setData($this->params()->fromPost());

            if($request->isXmlHttpRequest()) {
                if($settingsForm->isValid()) {
                    if($this->params()->fromPost('requestType') == 'submit') {
                        $module->unserializeArray($settingsForm->getData());
                        $module->save();
                        $resp = array('id' => $module->getId());
                    } else {
                        $resp = array();
                    }
                } else {
                    $resp['errors'] = $settingsForm->getMessages();
                }

                $jsonModel = new JsonModel($resp);
                return $jsonModel;
            }
        }

        $settingsForm->setData($module->serializeArray());

        $this->view->setVariables(array(
            'settingsForm' => $settingsForm,
            'header'      => $header,
        ));

        $this->viewHelper('headScript')
            ->appendFile('/engine/js/ckfinder/ckfinder.js')
            ->appendFile('/engine/js/jquery/spellchecker.js')
            ->appendFile('/engine/js/ckeditor/ckeditor.js')
            ->appendFile('/engine/js/jquery/form-validator.js')
            ->appendFile('/engine/js/form.js')
            ->appendFile('/engine/js/page-settings.js');

        $this->viewHelper('headLink')
            ->prependStylesheet('/engine/css/jquery/spellchecker.css');

        return $this->view;
    }

    public function deleteAction()
    {
        $id = $this->params()->fromPost('id');

        $model = $this->getService()->getModel();

        $resp = array(
            'status'    => $model->setId($id)->remove()
        );

        $jsonModel = new JsonModel($resp);
        return $jsonModel;
    }

    public function sortAction()
    {
        $modelService = $this->getService();

        $sort = $_POST['sort'];

        $modelService->updateSort($sort);

        $jsonModel = new JsonModel($sort);

        return $jsonModel;
    }

    public function fieldSwitchAction()
    {
        $modelService = $this->getService();

        $id    = $this->params()->fromPost('id');
        $field = $this->params()->fromPost('field');
        $val   = $this->params()->fromPost('val');

        $modelService->fieldSwitch($id, $field, $val);

        return new JsonModel();
    }

    public function autocompleteAction()
    {
        $modelService = $this->getService();

        $collection = $modelService->getModel()->getEntityCollection();

        $collection->select()
            ->limit(10)
            ->where->like('name', '%' . $this->params()->fromPost('query') . '%');

        $resp = [];
        foreach ($collection as $entity) {
            $resp[] = [
                'id'    => $entity->getId(),
                'label' => $entity->get('name'),
            ];
        }

        return new JsonModel($resp);
    }

    public function generate($uri = null)
    {
        $engine = new \StdClass();

        $module = $this->getServiceLocator()->get('Application\Model\Module');

        $engine->module = $module;

        $this->view->setVariable('engine', $engine);
        $this->view->setVariable('module', $module);
        $meta = new \StdClass();

        $meta->title  = $module->get('name');

        $this->layout()->setVariables([
            'meta'   => $meta,
            'header' => 'CMS',
            'engine' => $engine,
        ]);
    }

    /**
     * @return \Application\Model\Module
     */
    protected function getModule()
    {
        return $this->getServiceLocator()->get('Application\Model\Module');
    }

    /**
     * @return TableService
     */
    protected function getService()
    {
        $module = $this->getServiceLocator()->get('Application\Model\Module');

        $serviceClassName = ucfirst($module->get('module')) . 'Admin\\Service\\' . ucfirst($module->get('section')) . 'Service';

        $service = $this->getServiceLocator()->get($serviceClassName)
            ->setModuleName($module->get('module'))
            ->setSectionName($module->get('section'));

        return $service;
    }

    protected function setFields($fields) {
        $this->fields = $fields;
    }

    /**
     * @param $helperName
     * @param $helperData
     * @return AbstractHelper
     */
    public function viewHelper($helperName, $helperData = null)
    {
        $viewHelperManager = $this->getServiceLocator()->get('ViewHelperManager');
        $escapeHtml = $viewHelperManager->get($helperName);
        return $escapeHtml($helperData);
    }
}