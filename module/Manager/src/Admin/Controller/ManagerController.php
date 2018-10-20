<?php
namespace ManagerAdmin\Controller;

use Aptero\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class ManagerController extends AbstractActionController
{
    public function syncAction()
    {
        $date = $this->params()->fromQuery('datetime');

        $resp = [
            'tasks' => $this->getManagerService()->getTasks(['datetime' => $date])
        ];

        return new JsonModel($resp);
    }

    /**
     * @return \ManagerAdmin\Service\ManagerService
     */
    public function getManagerService()
    {
        return $this->getServiceLocator()->get('ManagerAdmin\Service\ManagerService');
    }
}