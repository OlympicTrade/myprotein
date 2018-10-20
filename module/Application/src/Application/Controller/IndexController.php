<?php
namespace Application\Controller;

use Application\Model\Region;
use Aptero\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $this->generate();

        $contacts = $this->layout()->getVariable('contacts');

        $productsService = $this->getProductsService();

        $products = $productsService->getProducts(array(
            'sort'      => 'popular',
            'join'      => ['reviews', 'image'],
            'limit'     => 6
        ));

        $articles = $this->getBlogService()->getArticles(['limit' => 3]);

        $discount = $this->getDiscountsService()->getActiveDiscount();

        return array(
            'products'      => $products,
            'contacts'      => $contacts,
            'articles'      => $articles,
            'discount'      => $discount,
        );
    }

    public function regionsAction()
    {
        if(!$this->getRequest()->isXmlHttpRequest()) {
            return $this->send404();
        }

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array(
            'region'   => Region::getInstance(),
            'regions'  => Region::getEntityCollection(),
            'reload'   => $this->params()->fromQuery('reload', true),
        ));

        return $view;
    }

    public function aboutAction()
    {
        $this->generate();

        return array(
            'header'      => $this->layout()->getVariable('header'),
            'breadcrumbs' => $this->getBreadcrumbs(),
        );
    }

    public function sitemapAction()
    {
        $sitemapXml = $this->getSitemapService()->generateSitemap();
        header('Content-type: application/xml');
		
		file_put_contents(PUBLIC_DIR . '/sitemap.xml', $sitemapXml);
        die($sitemapXml);
    }

    public function robotsAction()
    {
        $settings = $this->getServiceLocator()->get('Settings');
        header('Content-type: text/plain');
        die($settings->get('robots'));
    }

    public function pageAction()
    {
        $this->generate();

        $layout = $this->layout();

        $page = $layout->getVariable('page');

        if($page->get('redirect_url')) {
            return $this->redirect()->toUrl($page->get('redirect_url'));
        }

        if(!$page->getId()) {
            $response = $this->getResponse();
            $response->setStatusCode(404);
            $response->sendHeaders();
        }

        $view = new ViewModel();
        if ($this->getRequest()->isXmlHttpRequest()) {
            $view->setTemplate('application/index/page-ajax');
            $view->setTerminal(true);


            $view->setVariables(array(
                'header'  => $layout->getVariable('header'),
                'text'    => $layout->getVariable('page')->get('text'),
            ));
        }

        return $view;
    }

    /**
     * @return \Application\Service\SitemapService
     */
    protected function getSitemapService()
    {
        return $this->getServiceLocator()->get('Application\Service\SitemapService');
    }

    /**
     * @return \Catalog\Service\CatalogService
     */
    protected function getCatalogService()
    {
        return $this->getServiceLocator()->get('Catalog\Service\CatalogService');
    }

    /**
     * @return \Catalog\Service\ProductsService
     */
    protected function getProductsService()
    {
        return $this->getServiceLocator()->get('Catalog\Service\ProductsService');
    }

    /**
     * @return \Blog\Service\BlogService
     */
    protected function getBlogService()
    {
        return $this->getServiceLocator()->get('Blog\Service\BlogService');
    }

    /**
     * @return \Discounts\Service\DiscountsService
     */
    protected function getDiscountsService()
    {
        return $this->getServiceLocator()->get('Discounts\Service\DiscountsService');
    }
}