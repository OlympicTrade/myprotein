<?php
namespace News\Controller;

use Aptero\Mvc\Controller\AbstractActionController;

use News\Model\News;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class NewsController extends AbstractActionController
{
    public function indexAction()
    {
        $this->generate();
		
		$meta = $this->layout()->getVariable('meta');
		
		$page = (int) $this->params()->fromQuery('page', 1);
		if($page > 1) {
			$meta->title .= ' — страница ' . $page; 
		}
		
		$this->layout()->setVariable('meta', $meta);

        return array(
            'news' => $this->getNewsService()->getPaginator($page),
            'breadcrumbs'  => $this->getBreadcrumbs(),
        );
    }

    public function viewAction()
    {
        $this->generate('/news/');

        $url = $this->params()->fromRoute('url');

        $news = new News();
        $news->select()->where(array('url' => $url));

        if(!$news->load()) {
            return $this->send404();
        }

        $url = $this->url()->fromRoute('newsView', array('url' => $news->get('url')));

        $this->layout()->setVariable('canonical', $url);
        $this->layout()->setVariable('header', $news->get('name'));

        $this->addBreadcrumbs(array(array('url' => $url, 'name' => $news->get('name'))));

        $this->generateMeta(
            $news,
            array('{NEWS_NAME}', '{NEWS_DATE}', '{NEWS_AUTHOR}'),
            array($news->get('name'), $this->viewHelper('date', $news->get('date')), $news->get('author'))
        );

        $view = new ViewModel();
        $view->setTemplate('news/news/view');

        $view->setVariables(array(
            'breadcrumbs'  => $this->getBreadcrumbs(),
            'news'         => $news
        ));

        return $view;
    }

    /**
     * @return \News\Service\NewsService
     */
    public function getNewsService()
    {
        return $this->getServiceLocator()->get('News\Service\NewsService');
    }
}