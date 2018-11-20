<?php

namespace Catalog\Service;

use Aptero\Db\Entity\EntityFactory;
use Aptero\Mail\Mail;
use Aptero\String\Search;
use Catalog\Model\Reviews;
use Catalog\Model\Size;
use Catalog\Model\Taste;
use User\Service\AuthService;
use Zend\Db\Sql\Expression;
use Aptero\Service\AbstractService;
use Catalog\Model\Product;
use Zend\Json\Json;
use Zend\Paginator\Paginator;

class ProductsService extends AbstractService
{
    public function addReview($data)
    {
        if($user = AuthService::getUser()) {
           $data['user_id'] =  $user->getId();
        }

        $data['status'] =  Reviews::STATUS_NEW;

        $review = new Reviews();
        $review->setVariables($data)->save();

        $this->sendReviewMail($review);
    }

    public function sendReviewMail($review)
    {
        $feedbackModule = new \Application\Model\Module();
        $feedbackModule
            ->setModuleName('Contacts')
            ->setSectionName('Feedback')
            ->load();

        $siteSettings = $this->getServiceManager()->get('Settings');

        $mail = new Mail();
        $mail->setTemplate(MODULE_DIR . '/Catalog/view/catalog/mail/review.phtml')
            ->setHeader($siteSettings->get('site_name') . '. Новый отзыв')
            ->setVariables(array('review' => $review))
            ->addTo($feedbackModule->getPlugin('settings')->get('email'))
            ->send();
    }

    /*public function productsExcel($filters = array())
    {
        $excel = new \Aptero\MSWord\Excel();

        $time = \Catalog\Service\SyncService::getUpdateTime();
        $filename = 'Прайс от ' . $time;

        $category = new Catalog();
        $category->setId($filters['category']);
        $category->select()->where(array('active' => 1));
        $catalogService = $this->getServiceManager()->get('Catalog\Service\CatalogService');
        $filters['category'] = $catalogService->getCatalogIds($category);
		
        $excel
            ->setTitle($filename)
            ->setVal('Наименование')->setColWidth(1, 80)
            ->setVal('Производитель')->setColWidth(2, 16)
            ->setVal('Цена опт. (руб)')->setColWidth(3, 16)
            ->setVal('В наличии')->setColWidth(4, 16)
            ->nextRow();
        
        $select = $this->getProductsSelect($filters);
        $select->columns(array('name', 'count', 'price_opt'));
        $products = $this->execute($select);

        foreach($products as $product) {
            $excel
                ->setVal($product['name'])
                ->setVal($product['brand'])
                ->setVal($product['price_opt'])
                ->setVal(!$product['count'] || !$product['price_opt'] ? 'Нет' : 'Да')
                ->nextRow();
        }

        $excel->send($filename);
    }*/

    /**
     * @param $page
     * @param array $filters
     * @param int $itemsPerPage
     * @return Paginator
     */
    public function getPaginator($page, $filters = array(), $itemsPerPage = 12)
    {
        $itemsPerPage = max(24, min($itemsPerPage, 48));

        $filters['join'] = [
            'reviews',
            'catalog',
            'brand',
            'image',
        ];

        $products = Product::getEntityCollection();
        $products->setSelect($this->getProductsSelect($filters));
		
		//$products->dump();die();

        return $products->getPaginator($page, $itemsPerPage);
    }

    public function getMinMaxPrice($filters = array())
    {
        $product = new Product();
        $select = $this->getProductsSelect($filters);
        $select->columns([
            'max' => new Expression('MAX(t.price)'),
            'min' => new Expression('MIN(t.price)')
        ]);

        return $product->fetchRow($select);
    }

    public function getProductsInfo($data)
    {
        if($products = $data['products']) {
            $resp = array();
            foreach($products as $product) {
                $resp[] = $this->getProductsInfo($product);
            }

            return $resp;
        }

        $id      = $data['product_id'];
        $tasteId = isset($data['taste_id']) ? $data['taste_id'] : null;
        $sizeId  = isset($data['size_id']) ? $data['size_id'] : null;
        $count   = isset($data['count']) ? $data['count'] : 1;

        $product = $this->getProduct([
            'id'        => $id,
            'taste_id'  => $tasteId,
            'size_id'   => $sizeId,
        ]);

        if(!$product->load()) {
            return false;
        }

        $variant = $product->get('name');

        $taste = new Taste();
        $taste->setId($tasteId);
        if($taste->load()) {
            $variant .= ' ' . $taste->get('name');
        }

        $size = new Size();
        $size->setId($sizeId);
        if($size->load()) {
            $variant .= ' ' . $size->get('name');
        }

        if($variant == $product->get('name')) {
            $variant = '';
        }

        $result = [
            'id'        => $product->getId(),
            'list'      => 'Order cart',
            'list_name' => 'Order cart',
            'name'      => $product->get('name'),
            'price'     => $product->get('price'),
            'price_old' => $product->get('price_old'),
            'brand'     => $product->getPlugin('brand')->get('name'),
            'catalog'   => $product->getPlugin('catalog')->get('name'),
            'variant'   => $variant,
            'quantity'  => $count,
        ];

        if(isset($data['count'])) {
            $result['count'] = $data['count'];
        }

        return $result;
    }

    /**
     * @param array $filters
     * @param array $extend
     * @return \Aptero\Db\Entity\EntityCollection
     * @throws \Aptero\Db\Exception\RuntimeException
     */
    public function getProducts($filters = [], $extend = [])
    {
        $product = new Product();

        foreach($extend as $prop) {
            $product->addProperty($prop);
        }

        $products = $product->getCollection();
        $products->setSelect($this->getProductsSelect($filters));

        //return $this->getProduct($filters, $extend)->getCollection();

        return $products;
    }

    /**
     * @param $filters
     * @param array $extend
     * @return Product
     * @throws \Aptero\Db\Exception\RuntimeException
     */
    public function getProduct($filters, $extend = [])
    {
        $product = new Product();

        foreach($extend as $prop) {
            $product->addProperty($prop);
        }

        $product->setSelect($this->getProductsSelect($filters));

        //die($product->dump());

        return $product;
    }

    public function getProductForView($filters)
    {
        $product = new Product();

        $product->addProperties(array(
            'size_id'     => [],
            'taste_id'    => [],
        ));

        $filters['join'] = [
            'brands',
            'reviews',
            'stock',
        ];

        $filters['sort'] = 'price';
        
        $filters['columns'] = [
        	'id', 'brand_id', 'catalog_id', 'name', 'preview', 'url', 'mp_url', 'text', 'video', 'title', 'description', 'discount',
            'tab1_title', 'tab1_description', 'tab1_keywords', 'tab1_url', 'tab1_header', 'tab1_text',
            'tab2_title', 'tab2_description', 'tab2_keywords', 'tab2_url', 'tab2_header', 'tab2_text',
            'tab3_title', 'tab3_description', 'tab3_keywords', 'tab3_url', 'tab3_header', 'tab3_text',
    	];
        

        $select = $this->getProductsSelect($filters);
        

        $product->setSelect($select);

        return $product;
    }

    public function getProductByOldUrl($url)
    {
        $product = new Product();
        $product->select()
            ->columns(array('id', 'url'))
            ->where(array('url_old' => $url));

        return $product->get('url');
    }

    public function updateProductsStatistic()
    {
        $update = $this->getSql()->update();
        $update
            ->table('products')
            ->set(['popularity' => 0]);

        $this->execute($update);

        $select =
            $this->getSql()->select()
            ->from(['o' => 'orders'])
            ->columns(['id', 'count' => new Expression('count(DISTINCT o.id)')])
            ->join(['c' => 'orders_cart'], 'c.order_id = o.id', [])
            ->join(['p' => 'products'], 'c.product_id = p.id', ['id'])
            ->group('p.id')
            ->order('count DESC');

        $dt = new \DateTime();

        $select->where
            ->lessThanOrEqualTo('o.time_create', $dt->format('Y-m-d'))
            ->greaterThanOrEqualTo('o.time_create', $dt->modify('-2 months')->format('Y-m-d'));

        $result = $this->execute($select);

        foreach ($result as $row) {
            $update =
                $this->getSql()->update()
                ->table('products')
                ->set(['popularity' => $row['count']])
                ->where(['id' => $row['id']]);

            $this->execute($update);
        }
    }

    public function getRecoProducts($product)
    {
        $products = EntityFactory::collection(new Product());

        $select = $this->getProductsSelect(array(
            'category'  => $product->get('catalog_id'),
			'join' => array('reviews')
        ));
		
        $select
            ->order(new Expression('RAND()'))
            ->limit(10)
            ->where->notEqualTo('t.id', $product->getId());

        $products->setSelect($select);

        return $products;
    }

    public function getViewedProducts($product = null)
    {
        if(!isset($_COOKIE['viewed-products'])) {
            return array();
        }

        if(!$cookie = Json::decode($_COOKIE['viewed-products'])) {
            return array();
        }

        foreach($cookie as $cProduct) {
            if(!$id = (int) $cProduct->id) {
                continue;
            }
            $ids[] = $id;
        }

        if(!$ids) {
            return array();
        }

        $products = EntityFactory::collection(new Product());

        $select = $this->getProductsSelect(array('join' => array('reviews')))
            ->limit(10);

        $select->where->in('t.id', $ids);

        if($product) {
            $select->where->notEqualTo('t.id', $product->getId());
        }

        $products->setSelect($select);

        return $products;
    }

    public function getProductsBrands($filters)
    {
        $product = new Product();

        $select = $this->getProductsSelect($filters);
        $select->join(array('b2' => 'products_brands'), 't.brand_id = b2.id', array('brand_name' => 'name', 'brand_url' => 'url'));
        $select->group('t.brand_id');

        return $product->fetchAll($select);
    }

    public function getProductsCategories($filters)
    {
        $product = new Product();

        $select = $this->getProductsSelect($filters);
        $select->join(array('c2' => 'catalog'), 't.catalog_id = c2.id', array('category_name' => 'name', 'category_url' => 'url_path'));
        $select->group('t.catalog_id');

        return $product->fetchAll($select);
    }

    public function getProductsSelect($filters = array())
    {
        $filters = array_merge([
            'group'     => 't.id',
            'minPrice'  => true,
            'join'      => [],
            'columns'   => ['id', 'catalog_id', 'brand_id', 'name', 'discount', 'url'],
        ], $filters);

        $columns = $filters['columns'];

        if($filters['minPrice']) {
            $stSelect = $this->getSql()->select()
                ->from(['ps2' => 'products_stock'])
                ->columns(['stock' => new Expression('IF(MAX(ps2.count) >= 1,1,0)')])
                ->where([
                    't.id' => new Expression('ps2.product_id'),
                ]);

            $siSelect = $this->getSql()->select()
                ->from(['ps' => 'products_size'])
                ->columns(['price' => new Expression('MIN(ps.price)')])
                ->where(['t.id' => new Expression('ps.depend')]);

            $rSelect = $this->getSql()->select()
                ->from(['pt' => 'products_taste'])
                ->columns(['coefficient' => new Expression('MIN(pt.coefficient)')])
                ->where([
                    't.id' => new Expression('pt.depend'),
                ]);

            $columns['price_base'] = $siSelect;
            $columns['stock'] = $stSelect;
            $columns['coefficient'] = $rSelect;
        }

        $select = $this->getSql()->select()
            ->from(['t' => 'products']);

        if($filters['size_id'] && $filters['taste_id']) {
            $select
                ->join(['pp' => 'products_size'],  new Expression('t.id = pp.depend AND pp.id = ' . $filters['size_id']), ['price_base' => 'price', 'size_id' => 'id', 'size' => 'name'])
                ->join(['pt' => 'products_taste'], new Expression('t.id = pt.depend AND pt.id = ' . $filters['taste_id']), ['taste_id' => 'id', 'coefficient', 'taste' => 'name'])
                ->join(['ps' => 'products_stock'], new Expression('t.id = ps.product_id AND ps.taste_id = ' . $filters['taste_id'] .' AND ps.size_id = ' . $filters['size_id']), ['stock' => 'count'], 'left')
                ->where([
                    'pp.id' => $filters['size_id'],
                    'pt.id' => $filters['taste_id']
                ]);
        }

        if($filters['yandexYmlFull']) { //Для Яндекс Маркета
            $select
                //->where(['t.ya_market' => '1'])
                ->join(['ps'  => 'products_stock'], 't.id = ps.product_id', ['stock' => 'count', 'stock_id' => 'id'], 'left')
                ->join(['pss' => 'products_size'],  'pss.id = ps.size_id', [ 'size_id' => 'id', 'price_base' => 'price', 'size' => 'name'], 'left')
                ->join(['pst' => 'products_taste'], 'pst.id = ps.taste_id', ['taste_id' => 'id', 'coefficient', 'taste' => 'name'], 'left');
        }

        if($filters['yandexYml']) { //Для Яндекс Маркета
            $select
                ->join(['ps'  => 'products_stock'], 't.id = ps.product_id', ['stock' => 'count', 'stock_id' => 'id'], 'left');
        }

        /*if(!empty($filters['taste_id'])) {
            $select->join(['pt' => 'products_taste'], new Expression('t.id = pp.depend AND pt.id = ' . $filters['taste_id']), ['taste_id' => 'id'], 'left');
        }
*/
        if(isset($filters['join'])) {
            if (in_array('reviews', $filters['join'])) {
                $select
                    ->join(['pr' => 'products_reviews'], new Expression('t.id = pr.product_id AND pr.status = ' . Reviews::STATUS_VERIFIED), [
                        'stars'   => new Expression('AVG(pr.stars)'),
                    ], 'left');
            }
			
            if (in_array('brand', $filters['join'])) {
                $select
                    ->join(['pb' => 'products_brands'], 't.brand_id = pb.id', ['brand-name' => 'name', 'brand-id' => 'id']);
            }

            if (in_array('image', $filters['join'])) {
                $select
                    ->join(['pi' => 'products_images'], 't.id = pi.depend', ['image-id' => 'id', 'image-filename' => 'filename'], 'left');
            }

            if (in_array('catalog', $filters['join'])) {
                $select
                    ->join(['pc' => 'catalog'], 't.catalog_id = pc.id', ['catalog-id' => 'id', 'catalog-name' => 'name', 'catalog-url' => 'url'], 'left');
            }
        }

        if($filters['group']) {
            $select->group($filters['group']);
        }

        if(!empty($filters['id'])) {
            $select->where(['t.id' => $filters['id']]);
        }

        if(!empty($filters['pid'])) {
            $select->where(['t.id' => $filters['pid']]);
        }

        if(!empty($filters['name'])) {
            $select->where(['t.name' => $filters['name']]);
        }

        if(!empty($filters['url'])) {
            $select->where(['t.url' => $filters['url']]);
        }

        if(!empty($filters['event'])) {
            $select->where(['event' => $filters['event']]);
        }

        if(!empty($filters['catalog'])) {
            $select->where(['catalog_id' => $filters['catalog']]);
        }

        if($filters['price']) {
            $sSelect = $this->getSql()->select()
                ->from(['ps' => 'products_size'])
                ->columns(['price' => new Expression('MIN(ps.price)')])
                ->where(['t.id' => new Expression('ps.depend')]);

            $tSelect = $this->getSql()->select()
                ->from(['pt' => 'products_taste'])
                ->columns(['price' => new Expression('MIN(pt.coefficient)')])
                ->where(['t.id' => new Expression('pt.depend')]);

            $priceSql = '((' . $this->getSql()->buildSqlString($sSelect) . ') * (' . $this->getSql()->buildSqlString($tSelect) . ')) * (1 - t.discount / 100)';

            if (!empty($filters['price']['min'])) {
                $select->where->greaterThanOrEqualTo(new Expression($priceSql), $filters['price']['min']);
            }

            if (!empty($filters['price']['max'])) {
                $select->where->lessThanOrEqualTo(new Expression($priceSql), $filters['price']['max']);
            }
        }

        if(!empty($filters['limit'])) {
            $select->limit($filters['limit']);
        }

        if(isset($filters['sort']) && $filters['sort'] !== null) {
            $select->order('stock DESC');

            switch($filters['sort']) {
                case 'discount':
                    $select->order('discount DESC');
                    break;
                case 'price_up':
                    $select->order('price_base ASC');
                    break;
                case 'price_down':
                    $select->order('price_base DESC');
                    break;
                case 'popularity':
                    $select
                        ->order('popularity DESC');
                    break;
                case 'rand':
                    $select->order(new Expression('RAND()'));
                    break;
                default:
                    $select->order('t.popularity DESC');
            };
        } elseif($filters['minPrice']) {
            $select
                ->order('stock DESC')
                ->order('popularity DESC');;
        }

        if($filters['query']) {
            $queries = Search::prepareQuery($filters['query']);

            $where = '';
            foreach($queries as $query) {
                $where .= ($where ? ') OR (' : '((') . 't.name LIKE "' . ('%' . $query . '%') . '"';
            }
            $where .= '))';
            $select->where($where);
        }
        
        $select->columns($columns);
        
        //echo $select->getSqlString();die();

        return $select;
    }
}