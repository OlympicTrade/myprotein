<?php
namespace Sync\Controller;

use Aptero\Mvc\Controller\AbstractActionController;

use Zend\View\Model\JsonModel;

class SyncController extends AbstractActionController
{
    public function stockAction()
    {
        $type = $this->params()->fromRoute('type');

        switch ($type) {
            /*case 'changes':
                $data = $this->getSyncService()->syncChanges();
                break;
            case 'erase':
                $data = $this->getSyncService()->eraseChanges();
                break;
            case 'data':
                $data = $this->getSyncService()->getChanges();
                break;*/
            case 'product':
                $id  = $this->params()->fromQuery('id');
                if(!$id) {
                    return $this->send404();
                }
                $data = $this->getSyncService()->getProductData($id);
                break;
            default:
                return $this->send404();
        }
        
        return new JsonModel($data);
    }
	
    public function tasksAction()
    {
        return $this->send404();
    }

    /**
     * @return \Sync\Service\SyncService
     */
    public function getSyncService()
    {
        return $this->getServiceLocator()->get('Sync\Service\SyncService');
    }
}