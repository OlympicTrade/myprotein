<?php
namespace User\Controller;

use Aptero\Mvc\Controller\AbstractActionController;

use User\Form\LoginForm;
use User\Form\RegistrationForm;
use User\Form\RemindForm;
use User\Form\ResetForm;
use User\Form\SearchForm;
use User\Form\UserEditForm;

use User\Form\UserSubscribeForm;
use User\Model\User;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

use User\Service\AuthService;

class UserController extends AbstractActionController
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
        $viewModel->setTemplate('user/user/profile.phtml');
        $viewModel->setVariables(array(
            'user' => $user,
            'breadcrumbs' => $this->layout()->getVariable('breadcrumbs'),
            'cart'  => $cart,
            'price' => $price
        ));

        return $viewModel;
    }

    public function profileAction()
    {
        $login = $this->params()->fromRoute('id');

        $authService = new AuthService();
        if($authService->getIdentity()->get('login') == $login) {
            $this->redirect()->toRoute('user');
        }

        $user = new User();
        $user->addFilter(array('login' => $login));

        if(!$login || !$user->load()) {
            return $this->send404();
        }

        $this->generate('/user/');

        $cartService = $this->getCartService();
        $cart  = $cartService->getCookieCart();
        $price = $cartService->getCartPrice($cart);

        $viewModel = new ViewModel();
        $viewModel->setTemplate('user/user/public-profile.phtml');
        $viewModel->setVariables( array(
            'user'  => $user,
            'cart'  => $cart,
            'price' => $price
        ));

        return $viewModel;
    }

    public function editAction()
    {
        $form = new UserEditForm();
        $request = $this->getRequest();
        $user = AuthService::getUser();

        $form->setModel($user);
        $form->setData($user->serializeArray());

        if($request->isPost()) {
            $form->setData($request->getPost());
            $form->setFilters(array(
                'adapter' => $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter')
            ));

            if ($form->isValid()) {
                $data = $form->getData();

                $user->unserializeArray($data);
                $user->save();
            }

            return $jsonModel = new JsonModel(array(
                'errors' => $form->getMessages()
            ));
        }

        $this->generate();

        $viewModel = new ViewModel();
        if ($request->isXmlHttpRequest()) {
            $viewModel->setTerminal(true);
        }

        $viewModel->setVariables(array(
            'form'  => $form,
            'user'  => $user,
            'breadcrumbs' => $this->layout()->getVariable('breadcrumbs')
        ));

        return $viewModel;
    }
    public function subscribeAction()
    {
        $form = new UserSubscribeForm();
        $request = $this->getRequest();
        $user = AuthService::getUser();

        $form->setModel($user);
        $form->setData($user->serializeArray());

        if($request->isPost()) {
            $form->setData($request->getPost());
            $form->setFilters(array(
                'adapter' => $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter')
            ));

            if ($form->isValid()) {
                $data = $form->getData();
                $user->unserializeArray($data);
                $user->save();
            }

            return $jsonModel = new JsonModel(array(
                'errors' => $form->getMessages()
            ));
        }

        $this->generate();

        $viewModel = new ViewModel();
        if ($request->isXmlHttpRequest()) {
            $viewModel->setTerminal(true);
        }

        $viewModel->setVariables(array(
            'form'  => $form,
            'user'  => $user,
        ));

        return $viewModel;
    }

    public function resetAction()
    {
        $this->generate();
        $type = $this->params()->fromQuery('type');
        if($type == 'success') {
            $viewModel = new ViewModel();
            $viewModel->setTemplate('user/user/reset-success.phtml');
            return $viewModel;
        }

        $login = $this->params()->fromQuery('login');
        $hash  = $this->params()->fromQuery('hash');

        if(!$user = $this->getUserService()->getResetUser($login, $hash)) {
            $this->redirect()->toRoute('home');
        }

        $form = new ResetForm();

        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setFilters();
            $form->setData($request->getPost());

            $errors = array();

            if ($form->isValid()) {
                if($this->params()->fromPost('requestType') != 'validate') {
                    $date = $form->getData();
                    $user->set('password', $date['password'])
                        ->save();
                }
            } else {
                $errors = $form->getMessages();
            }

            if ($this->getRequest()->isXmlHttpRequest()) {
                return new JsonModel(array(
                    'errors' => $errors
                ));
            }
        }

        $this->generate();

        return array(
            'form'  => $form
        );
    }

    public function remindAction()
    {
        $form = new RemindForm();

        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setFilters();
            $form->setData($request->getPost());

            $errors = array();

            if ($form->isValid()) {
                if($this->params()->fromPost('requestType') != 'validate') {
                    $data = $form->getData();
                    $result = $this->getUserService()->sendRemindMail($data['login']);

                    if(!$result) {
                        $form->get('login')->setMessages(array('Пользователь не найден'));
                        $errors = $form->getMessages();
                    }
                }
            } else {
                $errors = $form->getMessages();
            }

            if ($this->getRequest()->isXmlHttpRequest()) {
                return new JsonModel(array(
                    'errors' => $errors
                ));
            }
        }

        $viewModel = new ViewModel();
        $viewModel
            ->setVariables(array(
                'form'  => $form
            ));

        if ($request->isXmlHttpRequest()) {
            $viewModel->setTerminal(true);
            $viewModel->setVariables(['isAjax' => true]);
        } else {
            $this->generate();
            $viewModel->setVariables(['isAjax' => false]);
        }

        return $viewModel;
    }

    public function imageUpdateAction()
    {
        if(!$this->getRequest()->isXmlHttpRequest() || empty($_FILES['image'])) {
            return $this->send404();
        }

        $file = $_FILES['image'];

        $authService = new AuthService();
        $user = $authService->getIdentity();

        $imageMdl = $user->getPlugin('image');

        $status = $imageMdl->setImage(array('filepath' => $file['tmp_name']));
        $imageMdl->save();

        $jsonModel = new JsonModel(array(
            'status' => $status,
            'image' => $imageMdl->getImage('m')
        ));

        return $jsonModel;
    }

    public function registrationAction()
    {
        $authService = new AuthService();

        if($authService->getIdentity()) {
            $this->redirect()->toRoute('user');
        }

        $form = new RegistrationForm();

        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setData($request->getPost());

            $form->setFilters(array(
                'adapter' => $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter')
            ));

            if ($form->isValid()) {
                $userService = $this->getUserService();
                $data = $form->getData();
                $user = $this->getUserService()->registerUser($data);

                $userService->sendRegistrationMail($user, $data['password']);
            }

            return new JsonModel(array(
                'errors' => $form->getMessages()
            ));
        }


        $viewModel = new ViewModel();

        if ($request->isXmlHttpRequest()) {
            $viewModel->setTerminal(true);
            $viewModel->setVariables(['isAjax' => true]);
        } else {
            $this->generate();
            $viewModel->setVariables(['isAjax' => false]);
        }

        $viewModel->setVariables(array('form' => $form));

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
    
    public function loginSocialAction()
    {
        $socService = $this->getSocialService();
        $adapter = $socService->setAdapter($this->params()->fromRoute('id'));

        $auther = $socService->getAuther($adapter);

        if ($auther && $auther->authenticate()) {
            $auth = new AuthService();
            $auth->socAuthenticate([
                'provider'   => $auther->getProvider(),
                'social_id'  => $auther->getSocialId(),
                'name' 		 => $auther->getName(),
                'email' 	 => $auther->getEmail(),
                'url'        => $auther->getSocialPage(),
                'sex' 		 => $auther->getSex(),
                'image' 	 => $auther->getAvatar(),
            ]);
        }

        return $this->redirect()->toUrl('/');
    }

    public function loginAction()
    {
        $form = new LoginForm();

        $request = $this->getRequest();
        $errors = array();

        if ($request->isPost()) {
            $form->setFilters();
            $form->setData($request->getPost());

            $errors = array();

            if ($form->isValid()) {
                $formData = $form->getData();

                $auth = new AuthService();
                $auth->setCredentials($formData['login'], $formData['password']);
                $result = $auth->authenticate();

                if(!$result->isValid()) {
                    $errors = ['all' => ['Неверный логин или пароль']];
                }
            } else {
                $errors = ['all' => ['Неверный логин или пароль']];
            }

            if ($request->isXmlHttpRequest()) {
                return new JsonModel(['errors' => $errors]);
            }

            if (!$errors) {
                $this->redirect()->toRoute('user');
            }
        }

        $socService = $this->getSocialService();
        $socAdapters = $socService->getAdapters();

        $viewModel = new ViewModel();

        if ($request->isXmlHttpRequest()) {
            $viewModel->setTerminal(true);
            $viewModel->setVariables(['isAjax' => true]);
        } else {
            $this->generate();
            $viewModel->setVariables(['isAjax' => false]);
        }

        $viewModel->setVariables([
            'form'        => $form,
            'errors'      => $errors,
            'socAdapters' => $socAdapters,
        ]);

        return $viewModel;
    }

    public function searchAction()
    {
        $this->generate();

        $page = $this->params()->fromQuery('page', 1);
        $rows = $this->params()->fromQuery('rows', 1);

        $form = new SearchForm();

        $filters = array();

        $form->setData($_GET);
        $form->setFilters();

        if($form->isValid()) {
            $filters = $form->getData();
        }

        $users = $this->getUserService()->getPaginator($page, $rows, $filters);

        return array(
            'users' => $users,
            'form'  => $form
        );
    }

    public function logoutAction()
    {
        $authService = new AuthService();

        if($user = $authService->getIdentity()) {
            $authService->clearIdentity();
        }

        return $this->redirect()->toUrl('/');
        //$this->redirect()->toRoute('user', array('action' => 'login'));
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