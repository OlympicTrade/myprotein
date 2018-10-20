<?php
namespace Catalog\Controller;

use Application\Model\Region;
use Application\Model\Settings;
use Aptero\Mvc\Controller\AbstractActionController;

use Catalog\Form\AddOrderForm;
use Catalog\Form\DeliveryOrderForm;
use Catalog\Form\FastOrderForm;
use Catalog\Form\OrderForm;
use Catalog\Form\OrderStep1Form;
use Catalog\Form\OrderStep2Form;
use Catalog\Form\ProductRequestForm;
use Catalog\Model\Order;

use Delivery\Model\Delivery;
use User\Service\AuthService;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class OrdersController extends AbstractActionController
{
    public function abandonedOrdersAction()
    {
        $this->getOrdersService()->checkOrders();

        return $this->send404();
    }

    public function orderStatusAction()
    {
        $request = $this->getRequest();
        $view = new ViewModel();

        if ($request->isXmlHttpRequest()) {
            $view->setTerminal(true);
        }

        if($request->isPost()) {
            $orderId = $this->params()->fromPost('id');

            if(!$orderId) {
                return new JsonModel(['errors' => ['id' => ['isEmpty' => 'Введите номер заказа']]]);
            }

            $orderId = rtrim(substr($orderId, 4), '_');

            $order = new Order();
            $order->setId($orderId);

            if(!$order->load()) {
                return new JsonModel(['errors' => ['id' => ['notFound' => 'Заказ не найден']]]);
            }

            return new JsonModel(['html' => $this->viewHelper('orderInfo', $order)]);
        }

        return $view;
    }

    public function productRequestAction()
    {
        $request = $this->getRequest();

        $extend = array('size_id', 'taste_id');

        if ($request->isPost()) {
            $product = $this->getProductsService()->getProduct($this->params()->fromPost(), $extend);

            if (!$product->load()) {
                return $this->send404();
            }

            $form = new ProductRequestForm();
            $form->setData($this->params()->fromPost());
            $form->setFilters();

            if ($form->isValid()) {
                return new JsonModel(array(
                    'id' => $this->getOrdersService()->newProductRequest($product, $form->getData())
                ));
            }

            return new JsonModel(array(
                'errors' => $form->getMessages()
            ));
        }

        $product = $this->getProductsService()->getProduct($this->params()->fromQuery(), $extend);
        $product->addProperty('size_id');
        $product->addProperty('taste_id');

        if (!$product->load()) {
            return $this->send404();
        }

        $viewModel = new \Zend\View\Model\ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setVariables(array(
            'product' => $product,
        ));

        return $viewModel;
    }

    public function cartFormAction()
    {
        $id = $this->params()->fromQuery('pid');

        $product = $this->getProductsService()->getProduct(array('id' => $id));

        if (!$product->load()) {
            return $this->send404();
        }

        $viewModel = new \Zend\View\Model\ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setVariables(array(
            'product' => $product,
        ));

        return $viewModel;
    }

    public function ordersAction()
    {
        $this->generate('/catalog/orders/');

        $orders = $this->getOrdersService()->getOrders();

        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'orders'  => $orders,
        ));

        if($this->getRequest()->isXmlHttpRequest()) {
            $viewModel->setTerminal(true);
        }

        return $viewModel;
    }

    public function orderCartAction()
    {
        if(!$this->getRequest()->isXmlHttpRequest()) {
            return $this->send404();
        }

        $id = $this->params()->fromRoute('id');

        $order = new Order();
        $order->select()->where([
            'id'        => $id,
            'user_id'   => AuthService::getUser()->getId(),
        ]);

        if(!$order->load()) {
            return $this->send404();
        }

        $viewModel = new ViewModel();
        $viewModel
            ->setTerminal(true)
            ->setVariables(array(
                'order'  => $order,
            ));

        return $viewModel;
    }

    public function orderAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost() || !$request->isXmlHttpRequest()) {
            $view = new ViewModel();
            $view->setTerminal(true);
            return $view;
        }

        $step = $this->params()->fromPost('step');
        $resp = [];

        if($step == 1) {
            $form = new OrderStep1Form();
            $form->setFilters();
            $form->setData($this->params()->fromPost());
            if($form->isValid()) {
                $data = $form->getData();
                if($user = AuthService::getUser()) {
                    $user->getPlugin('attrs')
                        ->set('name', $data['attrs-name'])
                        ->set('phone', $data['phone'])
                        ->save();
                }

                $ordersService = $this->getOrdersService();
                $order = $ordersService->orderStep1($form->getData());
                $resp['oid']   = $order->getId();
                $resp['phone'] = (int) $order->getPlugin('phone')->get('confirmed');
            } else {
                $resp['oid'] = 0;
            }
        } elseif($step == 2) {
            $deliveryType = $this->params()->fromPost('attrs-delivery');

            $form = new OrderStep2Form($deliveryType);
            $form->setFilters($deliveryType);
            $form->setData($this->params()->fromPost());
            if($form->isValid()) {
                $ordersService = $this->getOrdersService();
                $order = $ordersService->orderStep2($form->getData());
                $resp['info'] = $order->getDeliveryInfo();
                $resp['status'] = 1;
            } else {
                $resp['errors'] = $form->getMessages();
            }
        } elseif($step == 3) {
            $code = $this->params()->fromPost('code');
            $oid = $this->params()->fromPost('oid');

            $ordersService = $this->getOrdersService();
            $resp['status'] = (int) $ordersService->orderStep3($oid, $code);
        }

        return new JsonModel($resp);
    }

    public function deliveryInfoAction()
    {
        $order = new Order();
        $order->setId($this->params()->fromPost('id'));

        if(!$order->load()) {
            return $this->send404();
        }

        $deliveryService = $this->getDeliveryService();

        $resp = [
            'delivery' => [
                'type'  => 'delivery',
                'courier'  => [
                    'date'     => $deliveryService->getDeliveryDates([], 'courier')->format('d.m'),
                    'delay'    => $deliveryService->getDeliveryDates(['format' => 'delay'], 'courier'),
                    'exclude'  => $deliveryService->getCourierExcludedDays(),
                    'price'    => $deliveryService->getDeliveryPrice(['price' => $order->get('income')], 'courier'),
                ],
                'pickup'  => [
                    'date'     => $deliveryService->getDeliveryDates([], 'pickup')->format('d.m'),
                    'price'    => $deliveryService->getDeliveryPrice(['price' => $order->get('income')], 'pickup'),
                    'points'   => $this->getDeliveryService()->getPickupCount($order),
                ],
            ],
        ];

        return new JsonModel($resp);
    }

    public function ecommerceInfoAction()
    {
        $order = new Order();
        $order->setId($this->params()->fromPost('id'));

        if(!$order->load()) {
            return $this->send404();
        }

        $products = [];
        foreach ($order->getPlugin('cart') as $cartRow) {
            $product = $cartRow->getPlugin('product');
            $products[] = [
                'id'        => $product->getId(),
                'name'      => $product->get('name'),
                'list_name' => 'Order cart',
                'list'      => 'Order cart',
                'brand'     => $product->getPlugin('brand')->get('name'),
                'category'  => $product->getPlugin('catalog')->get('name'),
                'variant'   => $cartRow->getProductVariant(),
                'quantity'  => $cartRow->get('count'),
                'price'     => $cartRow->get('price'),
            ];
        }

        $resp = [
            'orderId'         => $order->getId(),
            'transaction_id'  => $order->getId(),
            'affiliation' => Settings::getInstance()->get('site_name'),
            'currency' => 'RUB',
            'shipping' => $order->get('delivery_income'),
            'value'    => $order->getProfit(),
            'price'  => [
                'products' => $order->get('income'),
                'price'    => $order->get('delivery_income')
            ],
            'items' => $products,
        ];

        return new JsonModel($resp);
    }

    public function cartInfoAction()
    {
        if(!$this->getRequest()->isXmlHttpRequest()) {
            return $this->send404();
        }

        $cartService = $this->getCartService();
        $cartInfo = $cartService->getCartInfo();

        $jsonModel = new JsonModel($cartInfo);
        return $jsonModel;
    }

    public function cartAction()
    {
        $this->generate('/catalog/cart/');

        $request = $this->getRequest();

        if(!$request->isXmlHttpRequest() && AuthService::hasUser()) {
            $this->redirect()->toUrl('/user/');
        }

        $cartService = $this->getCartService();
        $cart  = $cartService->getCookieCart();
        $price = $cartService->getCartPrice($cart);

        $viewModel = new ViewModel();
        $viewModel->setVariables([
            'cart'   => $cart,
            'price'  => $price,
            'breadcrumbs'  => $this->getBreadcrumbs(),
            'header'       => $this->layout()->getVariable('header'),
        ]);

        return $viewModel;
    }

    /**
     * @return \Catalog\Service\CartService
     */
    protected function getCartService()
    {
        return $this->getServiceLocator()->get('Catalog\Service\CartService');
    }

    /**
     * @return \Catalog\Service\OrdersService
     */
    protected function getOrdersService()
    {
        return $this->getServiceLocator()->get('Catalog\Service\OrdersService');
    }

    /**
     * @return \Catalog\Service\ProductsService
     */
    protected function getProductsService()
    {
        return $this->getServiceLocator()->get('Catalog\Service\ProductsService');
    }

    /**
     * @return \Delivery\Service\DeliveryService
     */
    protected function getDeliveryService()
    {
        return $this->getServiceLocator()->get('Delivery\Service\DeliveryService');
    }
}