<?php
namespace ApplicationAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {

    }

    public function mobileAction()
    {
        return $this->redirect()->toUrl('/admin/catalog/orders/mobile/');
    }
}