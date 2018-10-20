<?php
namespace Reviews\Controller;

use Aptero\Mvc\Controller\AbstractActionController;

use Reviews\Form\ReviewForm;
use Reviews\Model\Review;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class ReviewsController extends AbstractActionController
{
    public function indexAction()
    {
        $this->generate();

        $reviews = Review::getEntityCollection();
        $reviews->select()
            ->order('time_create DESC')
            ->where(array('status' => Review::STATUS_VERIFIED));

        return array(
            'reviews' => $reviews,
            'page'    => $this->layout()->getVariable('page'),
            'header'  => $this->layout()->getVariable('header'),
            'breadcrumbs'    => $this->getBreadcrumbs()
        );
    }

    public function addReviewAction()
    {
        $request = $this->getRequest();

        if(!$request->isXmlHttpRequest()) {
            return $this->send404();
        }

        if ($request->isPost()) {
            $form = new ReviewForm();
            $form->setData($request->getPost())->setFilters();

            if ($form->isValid()) {
                $this->getReviewsService()->addReview($form->getData());
            }

            return new JsonModel(array(
                'errors' => $form->getMessages()
            ));
        }

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    /**
     * @return \Reviews\Service\ReviewsService
     */
    public function getReviewsService()
    {
        return $this->getServiceLocator()->get('Reviews\Service\ReviewsService');
    }
}