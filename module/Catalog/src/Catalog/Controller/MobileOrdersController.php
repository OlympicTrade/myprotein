<?php
namespace Catalog\Controller;

use Aptero\Mvc\Controller\AbstractMobileActionController;
use Catalog\Form\OrderForm;
use Catalog\Form\ProductRequestForm;
use Catalog\Model\Order;

use User\Service\AuthService;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class MobileOrdersController extends AbstractMobileActionController
{
    public function abandonedOrdersAction()
    {
        $this->getOrdersService()->checkAbandonedOrders();

        return $this->send404();
    }

    public function finishOrderAction()
    {
        $orderId = $this->params()->fromPost('orderId');

        $order = new Order();

        if(!$this->getRequest()->isXmlHttpRequest() || !$orderId || !$order->setId($orderId)->load()) {
            return $this->send404();
        }

        if($order->getPlugin('phone')->get('confirmed')) {
            $order->set('status', Order::STATUS_PROCESSING);
        } else {
            $order->set('status', Order::STATUS_NEW);
        }
        $order->save();

        $this->getOrdersService()->sendOrderMail($order);

        return new JsonModel([
            'msgs'  => $this->getOrdersService()->orderMsg($order)
        ]);
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

    public function fastOrderAction()
    {
        $request = $this->getRequest();

        $extend = array('size_id', 'taste_id');

        if ($request->isPost()) {
            $product = $this->getProductsService()->getProduct($this->params()->fromPost(), $extend);

            if (!$product->load()) {
                return $this->send404();
            }

            $count =  $this->params()->fromPost('count');

            $form = new FastOrderForm();
            $form->setData($this->params()->fromPost());
            $form->setFilters();

            if ($form->isValid()) {
                return new JsonModel(array(
                    'id' => $this->getOrdersService()->newFastOrder($product, $count, $form->getData())->getId()
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
            'count'   => $this->params()->fromQuery('count', 1),
        ));

        return $viewModel;
    }

    public function checkCodeAction()
    {
        $request = $this->getRequest();

        if (!$request->isXmlHttpRequest() || !$request->isPost()) {
            return $this->send404();
        }

        $orderId = $this->params()->fromPost('orderId');
        $code = $this->params()->fromPost('code');

        return new JsonModel([
            'status'    => (bool) $this->getOrdersService()->checkCode($orderId, $code)
        ]);
    }

    public function addOrderAction()
    {
        $request = $this->getRequest();

        if (!$request->isXmlHttpRequest() || !$request->isPost()) {
            return $this->send404();
        }

        $form = new OrderForm();
        $form->setData($this->params()->fromPost());
        $form->setFilters();

        if ($form->isValid()) {
            $order = $this->getOrdersService()->addOrder($form->getData());

            return new JsonModel(array(
                'id'     => $order->getId(),
                'price'  => $order->get('price'),
                'phone'  => $order->getPlugin('phone')->get('confirmed'),
            ));
        }

        return new JsonModel(array(
            'errors' => $form->getMessages()
        ));
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
        $order->select()->where(array(
            'id'        => $id,
            'user_id'   => AuthService::getUser()->getId(),
        ));

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
        $this->generate('/catalog/order/');
        $price = $this->getCartService()->getCookieCart();

        if(!$price) {
            $this->redirect()->toRoute('cart');
        }

        $form = new OrderForm();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setFilters();
            $form->setData($this->params()->fromPost());

            if($request->isXmlHttpRequest()) {
                $resp = array();
                if($form->isValid()) {
                    $data = $form->getData();

                    if($user = AuthService::getUser()) {
                        $user->getPlugin('attrs')
                            ->set('address', $data['attrs-address'])
                            ->set('phone', $data['attrs-phone'])
                            ->set('email', $data['attrs-email'])
                            ->save();
                    }

                    $ordersService = $this->getOrdersService();
                    $order = $ordersService->newOrder($form->getData());
                    $ordersService->sendOrderMail($order);

                    return new JsonModel(array(
                        'id' => $order->getId()
                    ));
                } else {
                    return new JsonModel(array(
                        'errors' => $form->getMessages()
                    ));
                }
            }
        } elseif($user = AuthService::getUser()) {
            $userAttrs = $user->getPlugin('attrs');

            $form->setData(array(
                'attrs-name'    => $userAttrs->get('surname') . ' ' . $userAttrs->get('name'),
                'attrs-email'   => $user->get('email'),
                'attrs-phone'   => $userAttrs->get('phone'),
                'attrs-address' => $userAttrs->get('address'),
            ));
        }

        return array(
            'price'   => $price,
            'form'    => $form
        );
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
        $viewModel->setVariables(array(
            'cart'   => $cart,
            'price'  => $price,
            'breadcrumbs'  => $this->getBreadcrumbs(),
            'header'       => $this->layout()->getVariable('header'),
        ));

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
}