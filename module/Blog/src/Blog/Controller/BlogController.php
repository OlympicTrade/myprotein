<?php
namespace Blog\Controller;

use Aptero\Mvc\Controller\AbstractActionController;

use Blog\Form\CommentForm;
use Blog\Model\Article;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class BlogController extends AbstractActionController
{
    public function indexAction()
    {
        $view = $this->generate();
        $page = $this->params()->fromQuery('page', 1);
        $view->setVariables(array(
            'articles' => $this->getBlogService()->getPaginator($page)
        ));

        return $view;
    }
    public function articleAction()
    {
        $view = $this->generate('/blog/');

        $url = $this->params()->fromRoute('url');

        $article = $this->getBlogService()->getArticle($url);

        if(!$article->load()) {
            return $this->send404();
        }

        $this->generateMeta($article);

        $url = $this->url()->fromRoute('blogArticle', array('url' => $article->get('url')));

        $this->layout()->setVariable('canonical', $url);
        $this->addBreadcrumbs(array(array('url' => $url, 'name' => $article->get('name'))));

        $recoArticles = $this->getBlogService()->getRecoArticles($article);

        $view->setVariables(array(
            'header'      => $article->get('name'),
            'breadcrumbs' => $this->getBreadcrumbs(),
            'article'     => $article,
            'recoArticles'  => $recoArticles,
        ));

        return $view;
    }

    public function addCommentAction()
    {
        $request = $this->getRequest();

        if(!$request->isXmlHttpRequest()) {
            return $this->send404();
        }

        if ($request->isPost()) {
            $form = new CommentForm();
            $form->setData($request->getPost())->setFilters();

            if ($form->isValid()) {
                $this->getBlogService()->addComment($form->getData());
            }

            return new JsonModel(array(
                'errors' => $form->getMessages()
            ));
        }

        $article = new Article();
        $article->setId($this->params()->fromQuery('aid'));
        if(!$article->load()) {
            $this->send404();
        }

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setVariables(array(
            'article'   => $article,
            'parent'    => $this->params()->fromQuery('pid'),
        ));

        return $viewModel;
    }

    /**
     * @return \Blog\Service\BlogService
     */
    public function getBlogService()
    {
        return $this->getServiceLocator()->get('Blog\Service\BlogService');
    }
}