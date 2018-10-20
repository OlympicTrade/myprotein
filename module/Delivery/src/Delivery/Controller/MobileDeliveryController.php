<?php
namespace Delivery\Controller;

use Aptero\Mvc\Controller\AbstractMobileActionController;
use Delivery\Model\Delivery;
use DeliveryAdmin\Model\Pickup;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Delivery\Model\Region;

class MobileDeliveryController extends AbstractMobileActionController
{
    public function regionsAction()
    {
        if(!$this->getRequest()->isXmlHttpRequest()) {
            return $this->send404();
        }

        $regions = Region::getEntityCollection();
        $regions->select()
            ->order('priority DESC')
            ->where
                ->notEqualTo('cities', 0);

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array(
            'regions'  => $regions,
            'reload'   => $this->params()->fromQuery('reload', true),
        ));

        return $view;
    }
	
    public function indexAction()
    {
        $this->generate();
        $view = new ViewModel();

        $request = $this->getRequest();

        if($request->isXmlHttpRequest()) {
            $view->setTerminal(true);
        }

        $view->setVariables(array(
            'ajax'     => $request->isXmlHttpRequest(),
            'delivery' => Delivery::getInstance(),
            'header'   => $this->layout()->getVariable('header'),
            'breadcrumbs'   => $this->getBreadcrumbs(),
        ));

        return $view;
    }

    /**
     * @return \Delivery\Service\DeliveryService
     */
    public function getDeliveryService()
    {
        return $this->getServiceLocator()->get('Delivery\Service\DeliveryService');
    }
}