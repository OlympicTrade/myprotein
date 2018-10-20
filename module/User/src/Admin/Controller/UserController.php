<?php
namespace UserAdmin\Controller;

use Aptero\Mvc\Controller\Admin\AbstractActionController;
use ContactsAdmin\Model\Subscribe;
use UserAdmin\Form\LoginForm;
use Zend\View\Model\JsonModel;
use Aptero\Service\Admin\TableService;
use User\Service\AuthService;

class UserController extends AbstractActionController
{
    public function __construct()
    {
        parent::__construct();

        $this->setFields(array(
            'name' => array(
                'name'      => 'ФИО',
                'type'      => TableService::FIELD_TYPE_LINK,
                'field'     => 'name',
                'width'     => '15',
            ),
            'phone' => array(
                'name'      => 'Телефон',
                'type'      => TableService::FIELD_TYPE_TEXT,
                'field'     => 'login',
                'filter'    => function($value, $row){
                    return '';
                    //return $row->getPlugin('phone')->get('phone');
                },
                'width'     => '15',
            ),
            'email' => array(
                'name'      => 'E-mail',
                'type'      => TableService::FIELD_TYPE_EMAIL,
                'field'     => 'email',
                'width' => '15'
            ),
            'soc' => array(
                'name'      => 'Соц. сеть',
                'type'      => TableService::FIELD_TYPE_EMAIL,
                'field'     => 'soc_url',
                'filter'    => function($value, $row) {
                    return $value ? '<a href="' . $value . '">Открыть</a>' : '';
                },
                'width' => '55'
            ),
        ));
    }


    /**
     * @var string
     */
    protected $headerField = 'login';

    public function subscribeAction()
    {
        header('Content-Type:text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename=emails.txt');

        $subscribe = Subscribe::getEntityCollection();
        $first = true;
        foreach($subscribe as $email) {
            echo ($first ? '' : "\n") . $email->get('email');
        }

        die();
    }

    public function loginAction()
    {
        $form = new LoginForm();

        if ($this->getRequest()->isXmlHttpRequest()) {
            $resp['login'] = 0;

            $form->setFilters();
            $form->setData($this->params()->fromPost());
            $form->isValid();

            if ($form->isValid()) {
                $formData = $form->getData();
                $auth = new AuthService();
                $auth->setCredentials($formData['login'], $formData['password']);
                $result = $auth->authenticate();

                if($result->isValid()) {
                    $resp['login'] = 1;
                }
            }

            return new JsonModel($resp);
        }

        $this->layout('layout/admin/login');
    }

    public function logoutAction()
    {
        $authService = new AuthService();
        $authService->clearIdentity();

        $this->redirect()->toRoute('adminUser', array('action/login'));
    }
}