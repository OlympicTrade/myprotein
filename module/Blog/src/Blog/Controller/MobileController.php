<?php
namespace Blog\Controller;

use Aptero\Mvc\Controller\AbstractMobileActionController;
use Blog\Model\Article;

class MobileController extends AbstractMobileActionController
{
    public function indexAction()
    {
        $view = $this->generate();
        $view->setTemplate('blog/blog/index');

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

    /**
     * @return \Blog\Service\BlogService
     */
    public function getBlogService()
    {
        return $this->getServiceLocator()->get('Blog\Service\BlogService');
    }
}