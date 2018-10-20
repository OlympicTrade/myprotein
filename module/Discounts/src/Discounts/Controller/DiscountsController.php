<?php
namespace Discounts\Controller;

use Aptero\Mvc\Controller\AbstractActionController;

use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class DiscountsController extends AbstractActionController
{
    public function updateDiscountAction()
    {
        $this->getDiscountsService()->updateDiscounts();
        
        return $this->send404();
    }

    /**
     * @return \Discounts\Service\DiscountsService
     */
    public function getDiscountsService()
    {
        return $this->getServiceLocator()->get('Discounts\Service\DiscountsService');
    }
}