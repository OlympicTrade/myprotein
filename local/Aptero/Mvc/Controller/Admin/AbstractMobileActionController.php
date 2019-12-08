<?php

namespace Aptero\Mvc\Controller\Admin;

use Zend\Mvc\Controller\AbstractActionController as ZendActionController;
use Aptero\Service\Admin\TableService;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class AbstractMobileActionController extends ZendActionController
{
    public function __construct() {
        parent::__construct();

        $this->setFields([
            'id' => [
                'width'     => '40',
            ],
            'name' => [
                'width'     => '60',
                'filter'    => function($row){
                    return '<span>' . $row->get('name') . '</span>';
                },
            ],
        ]);
    }

    public function searchAction()
    {
        $query = trim(urldecode($this->params()->fromQuery('query')));

        if($this->getRequest()->isXmlHttpRequest()) {
            $results = $this->getService()->getAutoComplete($query);
            return new JsonModel($results);
        }
    }

    public function editAction()
    {
        $view = new ViewModel();
        $view->setTerminal(true);

        $model = $this->getService()->getModel()->setId($this->params()->fromQuery('id'));

        $view->setVariables([
            'model' => $model,
        ]);

        return $view;
    }

    public function indexAction()
    {
        $module = $this->getModule();
        return $this->redirect()->toUrl('/admin/mobile/' .  $module->get('module') . '/' . $module->get('section') . '/list/');
    }

    public function listAction()
    {
        $filters = [
            'search' => trim(urldecode($this->params()->fromQuery('search'))),
            'status' => $this->params()->fromQuery('status'),
        ];

        if($this->getRequest()->isXmlHttpRequest()) {
            $results = $this->getService()->getAutoComplete($filters);
            return new JsonModel($results);
        }

        $view = $this->generate();

        $list = $this->getService()->getList('', '', $filters);

        $page = (int) $this->params()->fromQuery('page', 1);

        $list->setCurrentPageNumber($page);
        $list->setItemCountPerPage(20);

        $view->setVariables([
            'list'   => $list,
            'fields' => $this->fields,
        ]);

        return $view;
    }

    public function generate()
    {
        $this->layout('layout/admin/mobile');
        $view = new ViewModel();

        $view->setVariables([
            'module' => $this->getServiceLocator()->get('Application\Model\Module'),
        ]);

        return $view;
    }

    protected $fields = [];
    protected function setFields($fields) {
        $this->fields = $fields;
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
}