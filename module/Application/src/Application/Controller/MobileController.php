<?php
namespace Application\Controller;

use Aptero\Mvc\Controller\AbstractMobileActionController;
use Zend\View\Model\ViewModel;

class MobileController extends AbstractMobileActionController
{
    public function indexAction()
    {
        $this->generate();

        $contacts = $this->layout()->getVariable('contacts');

        $discount = $this->getDiscountsService()->getActiveDiscount();

        return array(
            'contacts'   => $contacts,
            'discount'   => $discount,
            'articles'   => $this->getBlogService()->getArticles(['limit' => 3]),
        );
    }
    public function aboutAction()
    {
        $this->generate();

        $view = new ViewModel();
        $view->setTemplate('application/index/about');
        $view->setVariables(array(
            'header'      => $this->layout()->getVariable('header'),
        ));

        return $view;
    }

    public function deliveryAction()
    {
        $this->generate('/delivery/');

        $page = $this->layout()->getVariable('page');

        return array(
            'page'     => $page,
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
