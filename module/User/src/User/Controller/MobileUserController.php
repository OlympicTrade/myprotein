<?php
namespace User\Controller;

use Aptero\Mvc\Controller\AbstractMobileActionController;
use Zend\View\Model\ViewModel;

use User\Service\AuthService;

class MobileUserController extends AbstractMobileActionController
{
    public function indexAction()
    {
        dd('qweqew');
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

    public function confirmAction()
    {
        $login = $this->params()->fromQuery('login');
        $hash = $this->params()->fromQuery('hash');

        if(!$login && !$hash) {
            $this->generate();
            $viewModel = new ViewModel();
            $viewModel->setTemplate('user/user/confirmation.phtml');
            return $viewModel;
        }

        $result = $this->getUserService()->activateUser($login, $hash);

        $viewModel = new ViewModel();
        switch($result) {
            case 1:
                $viewModel->setTemplate('user/user/activate-not-found.phtml');
                break;
            case 2:
                $viewModel->setTemplate('user/user/activate-already.phtml');
                break;
            case 3:
                $viewModel->setTemplate('user/user/activate-success.phtml');
                break;
        }

        $this->generate();

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