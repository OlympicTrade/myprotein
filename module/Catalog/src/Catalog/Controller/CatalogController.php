<?php
namespace Catalog\Controller;

use Aptero\Mvc\Controller\AbstractActionController;

use Catalog\Model\Catalog;
use Catalog\Model\Product;
use Catalog\Form\ReviewForm;
use Delivery\Model\Delivery;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class CatalogController extends AbstractActionController
{
    public function balanceAction()
    {
        $this->getProductsService()->getProductsBalance();
        return $this->send404();
    }

    public function updatePopularityAction()
    {
        $this->getProductsService()->updateProductsStatistic();
        return $this->send404();
    }

    public function indexAction()
    {
        $this->generate('/catalog/', false);

        $url = $this->params()->fromRoute('url');

        $catalogService = $this->getCatalogService();

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
        return $view->setVariables([
            'header'   => $this->layout()->getVariable('header'),
            'catalog'  => $catalog,
            'products' => $products,
			'page'     => $page,
            'breadcrumbs'   => $this->getBreadcrumbs(),
        ]);
    }

    public function popularProductsAction()
    {
        $url = $this->params()->fromRoute('url');
        $category = $this->getCatalogService()->getCategory(['url' => $url]);

        if(!$category->load()) {
            return $this->send404();
        }

        $categoryIds = $this->getCatalogService()->getCatalogIds($category);
        $sub = $this->params()->fromPost('sub') == 'true';

        $products = $this->getProductsService()->getProducts([
            'sort'      => 'popular',
            'catalog'   => $categoryIds,
            'join'      => ['reviews'],
            'limit'     => $sub ? 2 : 3
        ]);

        $html = '';
        $html .= '<div class="spacer"></div>';

        $helper = $this->getViewHelper('productItem');

        foreach($products as $product) {
            $html .= $helper($product, ['list' => 'search']);
        }
        
        die($html);
    }

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
        $view->setTemplate('catalog/catalog/category');

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
            'breadcrumbs'   => $this->getBreadcrumbs(),
        ]);
    }

    public function productsFilters($options)
    {
        $filters = $this->params()->fromQuery();

        if(!isset($filters['sort'])) {
            $filters['sort'] = 'popularity';
        }

        return $filters;
    }

    public function yandexMarkerAction()
    {
        $ymlService = $this->getServiceLocator()->get('Catalog\Service\YandexYml');

        header("Content-type: text/xml; charset=utf-8");

        echo $ymlService->getYML($this->params()->fromQuery());

        die();
    }

    public function googleMerchantAction()
    {
        $ymlService = $this->getServiceLocator()->get('Catalog\Service\GoogleMerchant');

        header("Content-type: text/xml; charset=utf-8");

        echo $ymlService->getYML();

        die();
    }

    public function searchAction()
    {
        $query = trim(urldecode($this->params()->fromQuery('query')));


        if($this->getRequest()->isXmlHttpRequest()) {
            $results = $this->getCatalogService()->getAutoComplete($query);
            return new JsonModel($results);
        }

        $category = $this->getCatalogService()->getCategoryByName($query);
        if($category->load()) {
            $this->redirect()->toUrl($category->getUrl());
        }

        $category = $this->getBrandsService()->getBrand(['query' => $query]);
        if($category->load()) {
            $this->redirect()->toUrl('/brands/' . $category->get('url') . '/');
        }

        $this->generate('/catalog/search/');

        if(empty($query)) {
            return array(
                'breadcrumbs' => $this->getBreadcrumbs(),
                'header'    => 'Поиск',
                'products'  => [],
                'catalog'   => [],
                'query'     => $query,
            );
        }

        $page = $this->params()->fromRoute('page', 1);
        $products = $this->getProductsService()->getPaginator($page, array('query' => $query));

        return array(
            'breadcrumbs' => $this->getBreadcrumbs(),
            'header'    => 'Поиск "' . $this->viewHelper('escapeHtml', $query) . '"',
            'products'  => $products,
            'catalog'   => $this->getProductsService()->getProductsCategories(['query' => $query]),
            'brands'    => $this->getProductsService()->getProductsBrands(['query' => $query]),
            'query'     => $query,
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
                $this->getProductsService()->addReview($form->getData());
            }

            return new JsonModel(array(
                'errors' => $form->getMessages()
            ));
        }

        $product = new Product();
        $product->setId($this->params()->fromQuery('pid'));
        if(!$product->load()) {
            $this->send404();
        }

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $viewModel->setVariables(array(
            'product'   => $product
        ));

        return $viewModel;
    }

    public function getPriceAction()
    {
        $request = $this->getRequest();

        if(!$request->isXmlHttpRequest()) {
            return $this->send404();
        }

        $data = $this->params()->fromPost();

        if(!(int) $data['product_id']) {
            return $this->send404();
        }

        $product = $this->getProductsService()->getProduct(array(
            'taste_id'  => $data['taste_id'],
            'size_id'   => $data['size_id'],
            'id'        => $data['product_id'],
        ));
        //getProductStock

        return new JsonModel(array(
            'price' => $product->get('price'),
            'price_old' => $product->get('price_old'),
            'stock' => $product->get('stock'),
        ));
    }

    public function getProductStockAction()
    {
        $request = $this->getRequest();

        if(!$request->isXmlHttpRequest()) {
            return $this->send404();
        }

        $data = $this->params()->fromPost();

        if(!(int) $data['product_id']) {
            return $this->send404();
        }

        $product = new Product();
        $product->setId($data['product_id']);

        $resp = [];

        foreach($product->getPlugin('size') as $size) {
            $sizeId = $size->getId();

            $resp[$sizeId] = [
                'taste' => [],
                'stock' => [],
                'id'    => $sizeId,
            ];

            $stock = 0;
            $product->clearPlugin('taste');
            foreach($product->getPlugin('taste', array('size_id' => $sizeId)) as $taste) {
                $resp[$sizeId]['taste'][$taste->getId()] = $taste->get('stock');
                $stock += $taste->get('stock');
            }

            $resp[$sizeId]['stock'] = $stock;
        }

        return new JsonModel($resp);
    }

    public function getProductInfoAction()
    {
        $products = $this->getProductsService()->getProductsInfo($this->params()->fromPost());

        return new JsonModel($products);
    }

    public function productAction()
    {
        $this->generate('/catalog/', false);

        $url = $this->params()->fromRoute('url');

        $productsService = $this->getProductsService();

        $filters = ['url' => $url];

        if(!empty($_GET['variation']) && preg_match('/^(\d+)-(\d+)$/', $_GET['variation'], $matches)) {
            $filters['size_id'] = $matches[1];
            $filters['taste_id'] = $matches[2];
        } else {
            $filters['minPrice'] = true;
        }

        $product = $productsService->getProductForView($filters);

        /*if(!$product->load()) {
            $newUrl = $productsService->getProductByOldUrl($url);
            if($newUrl) {
                return $this->redirect()->toUrl('/goods/' . $newUrl . '/');
            }

            return $this->send404();
        }*/

        $metaSearch  = array('{PRODUCT_NAME}', '{CATALOG_NAME}', '{BRAND_NAME}');
        $metaReplace = array($product->get('name'), $product->getPlugin('catalog')->get('name'), $product->getPlugin('brand')->get('name'));

        $tabUrl = $this->params()->fromRoute('tab', '');

        $tabs = [];
        
        $tabs[] = [
            'tab'    => 'default',
            'header' => '<i class="far fa-file-alt"></i> Описание',
            'url'    => '',
        ];

        $attrs = $product->getPlugin('attrs');

        for($i = 1; $i <= 3; $i++) {
            $tab = 'tab' . $i;
            if(!$attrs->get($tab . '_url')) { continue; }
            $header = $attrs->get($tab . '_header');

            switch ($header) {
                case 'Ингредиенты': $header = '<i class="fas fa-flask"></i> ' . $header; break;
                case 'Как принимать': $header = '<i class="fas fa-utensils"></i> ' . $header; break;
                //case '': $header = ' ' . $header; break;
                default: break;
            }

            $tabs[] = [
                'tab'    => $tab,
                'header' => $header,
                'url'    => $attrs->get($tab . '_url'),
            ];
        }

        $tabs[] = [
            'tab'    => 'reviews',
            'header' => '<i class="far fa-comment-alt"></i> Отзывы' . ($product->get('reviews') ? ' <span>(' . $product->get('reviews') . ')</span>' : ''),
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
            $viewHelper = $this->getSL()->get('ViewHelperManager')->get('productTabs');
            $html = $viewHelper($product, $tab['tab']);

            if($tab['url'] == $tabUrl) {
                switch($tab['tab']) {
                    case 'default':
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
                    case 'instruction':
                        $meta = $this->generateMeta(null, $metaSearch, $metaReplace, array('prefix' => $tab['tab'] . '_'));
                        break;
                    case 'certificate':
                        $meta = $this->generateMeta(null, $metaSearch, $metaReplace, array('prefix' => $tab['tab'] . '_'));
                        break;
                    case 'tab1':
                        $meta = $this->generateMeta(null, $metaSearch, $metaReplace, array(
                            'title'       => $attrs->get('tab1_title'),
                            'description' => $attrs->get('tab1_description'),
                        ));
                        break;
                    case 'tab2':
                        $meta = $this->generateMeta(null, $metaSearch, $metaReplace, array(
                            'title'       => $attrs->get('tab2_title'),
                            'description' => $attrs->get('tab2_description'),
                        ));
                        break;
                    case 'tab3':
                        $meta = $this->generateMeta(null, $metaSearch, $metaReplace, array(
                            'title'       => $attrs->get('tab3_title'),
                            'description' => $attrs->get('tab3_description'),
                        ));
                        break;
                    default:
                }

                $tabs[$key]['html'] = $html;

                if($this->getRequest()->isXmlHttpRequest() && $this->params()->fromQuery('view') == 'tab') {
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

        $recoProducts   = $productsService->getRecoProducts($product);
        $viewedProducts = $productsService->getViewedProducts($product);

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
        $this->addBreadcrumbs([['url' => $urlCatalog, 'name' => $product->get('name')]]);

        $view = new ViewModel();

        $view->setVariables([
            'breadcrumbs'  => $this->getBreadcrumbs(),
            'header'       => $product->get('name'),
            //'inCart'       => $this->getCartService()->checkInCart($product->getId()),
            'product'      => $product,
            'category'     => $category,
            'delivery'     => Delivery::getInstance(),
            'recoProducts'   => $recoProducts,
            'viewedProducts' => $viewedProducts,
            'tabs'         => $tabs,
            'tabUrl'       => $tabUrl,
        ]);

        if($this->getRequest()->isXmlHttpRequest()) {
            $view->setTerminal(true);
            $view->setTemplate('catalog/catalog/product-ajax.phtml');
        }

        return $view;
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