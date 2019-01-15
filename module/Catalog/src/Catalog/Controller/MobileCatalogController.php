<?php
namespace Catalog\Controller;

use Aptero\Mvc\Controller\AbstractMobileActionController;
use Catalog\Model\Catalog;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class MobileCatalogController extends AbstractMobileActionController
{
    public function indexAction()
    {
        $this->generate('/catalog/', false);

        $url = $this->params()->fromRoute('url');

        $catalogService = $this->getCatalogService();

        /*if($url) {
            $category = $catalogService->getCategory(array('url' => $url))->load();

            if($category) {
                return $this->categoryAction($category);
            }

            return $this->send404();
        }*/
        if($url) {
            $category = $catalogService->getCategory(['url' => $url]);

            if($category) {
                return $this->categoryAction($category);
            }

            $subUrl = substr($url, strrpos($url, '/') + 1);
            $categoryUrl = substr($url, 0, strrpos($url, '/'));

            if(!$category = $catalogService->getCategory(['url' => $categoryUrl])) {
                return $this->send404();
            }

            if($type = $catalogService->getTypeByUrl($category->getId(), $subUrl)) {
                return $this->categoryAction($category, ['type' => $type]);
            }

            return $this->send404();
        }

        /* Products */
        $page = $this->params()->fromQuery('page', 1);
        $catalog = Catalog::getEntityCollection();
        $catalog->setParentId(0);

        $filters = $this->params()->fromQuery();

        $products = $this->getProductsService()->getPaginator($page, $filters);

        $view = new ViewModel();
        return $view->setVariables(array(
            'header'   => $this->layout()->getVariable('header'),
            'catalog'  => $catalog,
            'products' => $products,
			'page'     => $page,
            'bLink'    => $this->getBreadcrumbs(),
        ));
    }

    /*public function categoryAction($category, $options = [])
    {
        $catalogService = $this->getCatalogService();

        $url = $this->url()->fromRoute('catalog', array('url' => $category->getUrl()));
        $this->layout()->setVariable('canonical', $url);
        $this->addBreadcrumbs($catalogService->getCategoryCrumbs($category));

        $this->generateMeta($category, array('{CATALOG_NAME}', '{CATALOG_NAME_L}'), array($category->get('name'), mb_strtolower($category->get('name'))));

        $category->clearSelect();
        $category->select()->where(array('active' => 1));
        $categoryIds = $catalogService->getCatalogIds($category);

        $view = new ViewModel();
        $view->setTemplate('catalog/mobile-catalog/category');

        $page = $this->params()->fromQuery('page', 1);
        $productsService = $this->getProductsService();
        $filter['catalog'] = $categoryIds;
        $products = $productsService->getPaginator($page, $filter);

        $bLink = $category->getParent() ? '/catalog/' .  $category->getParent()->get('url_path' . '/') : '/catalog/';

        return $view->setVariables(array(
            'header'   => $category->get('name'),
            'category' => $category,
            'products' => $products,
            'page'     => $page,
            'bLink'    => $bLink,
        ));
    }*/

    public function categoryAction($category, $options = [])
    {
        $catalogService = $this->getCatalogService();

        $type  = $options['type'] ?? null;

        if($type) {
            $this->generateMeta($type, ['{CATALOG_NAME}', '{CATALOG_NAME_L}'], [$category->get('name'), mb_strtolower($category->get('name'))]);
        } else {
            $this->generateMeta($category, ['{CATALOG_NAME}', '{CATALOG_NAME_L}'], [$category->get('name'), mb_strtolower($category->get('name'))]);
        }

        $meta = $this->layout()->getVariable('meta');
        $meta->title = $meta->title ? $meta->title : $category->get('header');

        $parent = $category;
        while($parent = $parent->getParent()) {
            $meta->keywords .= ', ' . $parent->get('name');
        }

        if($type) {
            $url = $type->getUrl();
        } else {
            $url = $category->getUrl();
        }

        $this->layout()->setVariable('canonical', $url);
        $this->addBreadcrumbs($catalogService->getCategoryCrumbs($category));

        if($type) {
            $header = $type->get('name');
            $this->addBreadcrumbs([['url' => $url, 'name' => $type->get('name')]]);
        } else {
            $header = $category->get('name');
        }

        $view = new ViewModel();
        $view->setTemplate('catalog/mobile-catalog/category');

        //Products
        $categoryIds = $catalogService->getCatalogIds($category);
        $page = $this->params()->fromQuery('page', 1);
        $productsService = $this->getProductsService();

        $filters = $this->params()->fromQuery();

        if($type) {
            $filters['type'] = $type->getId();
        }

        if(!isset($filters['sort'])) {
            $filters['sort'] = 'popularity';
        }

        $filters['catalog'] = $categoryIds;

        $products = $productsService->getPaginator($page, $filters);

        return $view->setVariables([
            'header'        => $header,
            'category'      => $category,
            'products'      => $products,
            'page'          => $page,
            'type'          => $type,
        ]);
    }

    public function yandexMarkerAction()
    {
        $ymlService = $this->getServiceLocator()->get('Catalog\Service\YandexYml');

        header("Content-type: text/xml; charset=utf-8");
        echo $ymlService->getYML();
        die();
    }

    public function productAction()
    {
        $this->generate('/catalog/', false);

        $url = $this->params()->fromRoute('url');

        $productsService = $this->getProductsService();
        $product = $productsService->getProductForView(array('url' => $url));

        if(!$product->load()) {
            return $this->send404();
        }

        $metaSearch  = array('{PRODUCT_NAME}', '{CATALOG_NAME}', '{BRAND_NAME}');
        $metaReplace = array($product->get('name'), $product->getPlugin('catalog')->get('name'), $product->getPlugin('brand')->get('name'));

        $tabUrl = $this->params()->fromRoute('tab', '');

        $tabs = [];

        $tabs[] = [
            'tab'    => 'default',
            'header' => 'Описание',
            'url'    => '',
        ];
        
        $tabs[] = [
            'tab'    => 'composition',
            'header' => 'Состав',
            'url'    => 'composition',
        ];

        for($i = 1; $i <= 3; $i++) {
            $tab = 'tab' . $i;
            if(!$product->get($tab . '_url')) { continue; }
            $tabs[] = [
                'tab'    => $tab,
                'header' => $product->get($tab . '_header'),
                'url'    => $product->get($tab . '_url'),
            ];
        }

        $tabs[] = [
            'tab'    => 'reviews',
            'header' => 'Отзывы' . ($product->get('reviews') ? ' <span>(' . $product->get('reviews') . ')</span>' : ''),
            'url'    => 'reviews',
        ];

        $tabs[] = [
            'tab'    => 'articles',
            'header' => 'Статьи',
            'url'    => 'articles',
        ];

        $tabs[] = [
            'tab'    => 'video',
            'header' => 'Видео',
            'url'    => 'video',
        ];

        $meta = null;

        foreach($tabs as $key => $tab) {
            $viewHelper = $this->getSL()->get('ViewHelperManager')->get('mobileProductTabs');
            $html = $viewHelper($product, $tab['tab']);

            if($tab['url'] == $tabUrl) {
                switch($tab['tab']) {
                    case 'default':
                        if($product->get('title')) {
                            $product->set('title', $product->get('title') . ' | {BRAND_NAME}');
                        }

                        if($product->get('keywords')) {
                            $product->set('keywords', $product->get('keywords') . ', спортивное питание, {BRAND_NAME}');
                        }

                        if($product->get('description')) {
                            $product->set('description', rtrim($product->get('description'), '. ') . '. Доставка по Москве и Санкт-Петербургу.');
                        }

                        $meta = $this->generateMeta($product, $metaSearch, $metaReplace);
                        break;
                    case 'video':
                        $meta = $this->generateMeta(null, $metaSearch, $metaReplace, array('prefix' => $tab['tab'] . '_'));
                        break;
                    case 'reviews':
                        $meta = $this->generateMeta(null, $metaSearch, $metaReplace, array('prefix' => $tab['tab'] . '_'));
                        break;
                    case 'articles':
                        $meta = $this->generateMeta(null, $metaSearch, $metaReplace, array('prefix' => $tab['tab'] . '_'));
                        break;
                    case 'composition':
                        $meta = $this->generateMeta(null, $metaSearch, $metaReplace, array('prefix' => $tab['tab'] . '_'));
                        break;
                    case 'instruction':
                        $meta = $this->generateMeta(null, $metaSearch, $metaReplace, array('prefix' => $tab['tab'] . '_'));
                        break;
                    case 'certificate':
                        $meta = $this->generateMeta(null, $metaSearch, $metaReplace, array('prefix' => $tab['tab'] . '_'));
                        break;
                    case 'tab1':
                        $meta = $this->generateMeta(null, $metaSearch, $metaReplace, array(
                            'title'       => $product->get('tab1_title'),
                            'description' => $product->get('tab1_description'),
                            'keywords'    => $product->get('tab1_keywords'),
                        ));
                        break;
                    case 'tab2':
                        $meta = $this->generateMeta(null, $metaSearch, $metaReplace, array(
                            'title'       => $product->get('tab2_title'),
                            'description' => $product->get('tab2_description'),
                            'keywords'    => $product->get('tab2_keywords'),
                        ));
                        break;
                    case 'tab3':
                        $meta = $this->generateMeta(null, $metaSearch, $metaReplace, array(
                            'title'       => $product->get('tab3_title'),
                            'description' => $product->get('tab3_description'),
                            'keywords'    => $product->get('tab3_keywords'),
                        ));
                        break;
                    default:
                }

                $tabs[$key]['html'] = $html;

                if($this->getRequest()->isXmlHttpRequest()) {
                    $resp = array(
                        'html'  => $html,
                        'meta'  => $meta,
                    );
                    return new JsonModel($resp);
                }
            }

            if(!$html) {
                unset($tabs[$key]);
                continue;
            }
        }

        if(!$meta) {
            $this->send404();
        }

        if(!$product->getId()) {
            $response = $this->getResponse();
            $response->setStatusCode(404);
            $response->sendHeaders();
        }

        $url = $this->url()->fromRoute('product', array('url' => $product->get('url')));

        $this->layout()->setVariable('header', $product->get('name'));
        $this->layout()->setVariable('canonical', $url);

        $category = $product->getPlugin('catalog');

        $urlCatalog = $this->url()->fromRoute('catalog', array('url' => $category->get('url')));

        $this->addBreadcrumbs($this->getCatalogService()->getCategoryCrumbs($category));
        $this->addBreadcrumbs(array(array('url' => $urlCatalog, 'name' => $category->get('name'))));

        return array(
            'bLink'        => '/catalog/' . $category->getUrl() . '/',
            'header'       => $product->get('name'),
            //'inCart'       => $this->getCartService()->checkInCart($product->getId()),
            'product'      => $product,
            'category'     => $category,
            'tabs'         => $tabs,
            'tabUrl'       => $tabUrl,
        );
    }

    /**
     * @return \Catalog\Service\BrandsService
     */
    protected function getBrandsService()
    {
        return $this->getServiceLocator()->get('Catalog\Service\BrandsService');
    }

    /**
     * @return \Catalog\Service\CartService
     */
    protected function getCartService()
    {
        return $this->getServiceLocator()->get('Catalog\Service\CartService');
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
}