<?php
namespace User\Controller;

use Aptero\Mvc\Controller\AbstractMobileActionController;
use Zend\View\Model\ViewModel;

use User\Service\AuthService;

class MobileUserController extends AbstractMobileActionController
{
    public function indexAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            die('<script>location.href = "/user/";</script>');
        }

        $this->generate();

        $authService = new AuthService();
        $user = $authService->getIdentity();

        $cartService = $this->getCartService();
        $cart  = $cartService->getCookieCart();
        $price = $cartService->getCartPrice($cart);

        $viewModel = new ViewModel();
        $viewModel->setTemplate('user/mobile/profile.phtml');
        $viewModel->setVariables([
            'user' => $user,
            'breadcrumbs' => $this->layout()->getVariable('breadcrumbs'),
            'cart'  => $cart,
            'price' => $price
        ]);

        return $viewModel;
    }

    /**
     * @return \User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->getServiceLocator()->get('User\Service\UserService');
    }

    /**
     * @return \Catalog\Service\CartService
     */
    protected function getCartService()
    {
        return $this->getServiceLocator()->get('Catalog\Service\CartService');
    }

    /**
     * @return \User\Service\SocialService
     */
    protected function getSocialService()
    {
        return $this->getServiceLocator()->get('User\Service\SocialService');
    }
}