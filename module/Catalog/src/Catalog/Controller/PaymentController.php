<?php
namespace Catalog\Controller;

use Aptero\Mvc\Controller\AbstractActionController;

use Aptero\String\Numbers;
use Catalog\Model\Catalog;
use Catalog\Model\Order;
use Catalog\Model\Price;
use Catalog\Model\Taste;
use Catalog\Model\Product;
use Catalog\Form\ReviewForm;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class PaymentController extends AbstractActionController
{
    public function payAction()
    {
        $orderId = $this->params()->fromQuery('o');

        $order = new Order();
        $order->setId($orderId);

        if(!$order->load()) {
            return $this->send404();
        }

        $view = $this->generate('/payment/');
        $view->setVariables([
            'order' => $order
        ]);

        if($order->isPaid()) {
            $view->setTemplate('/catalog/payment/paid');
        }
        
        return $view;
    }

    public function confirmAction()
    {
        $data = $this->params()->fromPost();

        $result = $this->getPaymentService()->payment($data);

        echo $result ? 'success' : 'fail';
        die();
    }

    public function failAction()
    {
        die('fail');
    }

    /**
     * @return \Catalog\Service\PaymentService
     */
    protected function getPaymentService()
    {
        return $this->getServiceLocator()->get('Catalog\Service\PaymentService');
    }

    /**
     * @return \Catalog\Service\OrdersService
     */
    protected function getOrdersService()
    {
        return $this->getServiceLocator()->get('Catalog\Service\OrdersService');
    }
}