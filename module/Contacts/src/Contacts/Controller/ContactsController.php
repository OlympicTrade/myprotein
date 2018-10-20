<?php
namespace Contacts\Controller;

use Aptero\Mvc\Controller\AbstractActionController;

use Contacts\Form\FeedbackForm;
use Contacts\Model\Contacts;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class ContactsController extends AbstractActionController
{
    public function indexAction()
    {
        $this->generate();

        $viewModel = new ViewModel();

        $request = $this->getRequest();

        if ($request->isPost()) {
            $form = new FeedbackForm();
            $form->setData($request->getPost());
            $form->setFilters();

            $errors = [];

            if (!$form->isValid()) {
                $errors = $form->getMessages();
            }

            $data = $form->getData();
            if(!$data['email'] && !$data['phone']) {
                $errors['phone'] = ['Укажите телефон или email'];
                $errors['email'] = ['Укажите телефон или email'];
            }

            if (!$errors) {
                $feedback = $this->getFeedbackService()->addMessage($form->getData());
                $this->getFeedbackService()->sendFeedbackMail($feedback);
            }

            return new JsonModel([
                'errors' => $errors
            ]);
        } elseif($request->isXmlHttpRequest()) {
            $viewModel->setTerminal(true);
            $viewModel->setTemplate('/contacts/contacts/feedback.phtml');
        } else {
            $viewModel->setVariables([
                'header'        => $this->layout()->getVariable('header'),
                'breadcrumbs'   => $this->getBreadcrumbs(),
            ]);
        }

        return $viewModel;
    }

    public function feedbackAction()
    {
        $request = $this->getRequest();

        if(!$this->getRequest()->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('contacts');
        }

        $form = new FeedbackForm();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            $form->setFilters();

            $errors = array();

            if (!$form->isValid()) {
                $errors = $form->getMessages();
            }

            $data = $form->getData();
            if(!$data['email'] && !$data['phone']) {
                $errors['phone'] = array('Укажите телефон или email');
                $errors['email'] = array('Укажите телефон или email');
            }

            if (!$errors) {
                $feedback = $this->getFeedbackService()->addMessage($form->getData());
                $this->getFeedbackService()->sendFeedbackMail($feedback);
            }

            return new JsonModel(array(
                'errors' => $errors
            ));
        }

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setVariables(array(
            'form' => $form
        ));

        return $viewModel;
    }

    public function uploadAction()
    {
        if(isset($_FILES['upl']) && $_FILES['upl']['error'] == 0){
            if(move_uploaded_file($_FILES['upl']['tmp_name'], DATA_DIR . '/uploads/'.$_FILES['upl']['name'])){
                return new JsonModel(array('status' => 'success'));
            }
        }

        return new JsonModel(array('status' => 'error'));
    }

    /**
     * @return \Contacts\Service\FeedbackService
     */
    protected function getFeedbackService()
    {
        return $this->getServiceLocator()->get('Contacts\Service\FeedbackService');
    }
}