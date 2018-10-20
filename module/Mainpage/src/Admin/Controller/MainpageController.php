<?php
namespace MainpageAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;

use Zend\View\Model\JsonModel;

use Aptero\Service\Admin\TableService;

class MainpageController extends AbstractActionController
{

    public function indexAction()
    {
        $module = $this->getServiceLocator()->get('Application\Model\Module')->load();

        return $this->redirect()->toRoute('admin', array(
            'module' => $module->get('module'),
            'section' => $module->get('section'),
            'action' => 'edit',
        ), array('query' => array('id' => 1)));
    }
}