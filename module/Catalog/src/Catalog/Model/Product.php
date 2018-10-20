<?php
namespace Catalog\Model;

use Aptero\Db\Entity\Entity;
use Aptero\Db\Entity\EntityFactory;
use Blog\Model\Article;
use Zend\Db\Sql\Predicate\Expression as PredicateExpression;
use CatalogAdmin\Model\Plugin\ProductImages;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;

class Product extends Entity
{
    static public $events = array(
        'top'    => 'Лидеры продаж',
        'new'    => 'Новинки',
        'sale'   => 'Распродажа',
    );

    public function __construct()
    {
        $this->setTable('products');

        $this->addProperties(array(
            'parent'            => [],
            'name'              => [],
            'preview'           => [],
            'url'               => [],
            'mp_url'            => [],
            'energy'            => [],
            'ingredients'       => [],
            'catalog_id'        => [],
            'brand_id'          => [],
            'text'              => [],
            'discount'          => [],
            'video'             => [],
            'title'             => [],
            'description'       => [],
            'keywords'          => [],

            'tab1_title'        => [],
            'tab1_description'  => [],
            'tab1_keywords'     => [],
            'tab1_url'          => [],
            'tab1_header'       => [],
            'tab1_text'         => [],

            'tab2_title'        => [],
            'tab2_description'  => [],
            'tab2_keywords'     => [],
            'tab2_url'          => [],
            'tab2_header'       => [],
            'tab2_text'         => [],

            'tab3_title'        => [],
            'tab3_description'  => [],
            'tab3_keywords'     => [],
            'tab3_url'          => [],
            'tab3_header'       => [],
            'tab3_text'         => [],

            'price'             => ['virtual' => true],
            'price_old'         => ['virtual' => true],
            'price_base'        => ['virtual' => true],
            'weight'            => ['virtual' => true],
            'stars'             => ['virtual' => true],
            'reviews'           => ['virtual' => true],
            'coefficient'       => ['virtual' => true],
            'count'             => ['virtual' => true],
            'stock'             => ['virtual' => true],
            'event'             => [],
            'time_update'       => [],
            'sort'              => [],
            'popularity'        => [],
        ));

        $this->addPropertyFilterOut('price', function($model) {
            $price = $model->get('price_base') * $model->get('coefficient');
            $price -= $price * ($model->get('discount') / 100);

            return ceil($price / 10) * 10;
        });

        $this->addPropertyFilterOut('price_old', function($model) {
            $price = $model->get('price_base') * $model->get('coefficient');
            return ceil($price / 10) * 10;
        });

        $this->addPlugin('articles', function($model) {
            $catalog = Article::getEntityCollection();
            $catalog->select()
                ->join(array('pa' => 'products_articles') , new Expression('pa.article_id = t.id AND pa.depend = ' . $model->getId()), []);

            return $catalog;
        });

        $this->addPlugin('attrs', function() {
            $properties = new \Aptero\Db\Plugin\Attributes();
            $properties->setTable('products_attrs');

            return $properties;
        });

        $this->addPlugin('recommended', function($model) {
            $catalog = Product::getEntityCollection();

            $sSelect = $this->getSql()->select()
                ->from(array('ps2' => 'products_stock'))
                ->columns(array('stock' => new Expression('IF(MAX(ps2.count) >= 1,1,0)')))
                ->where(array(
                    't.id' => new Expression('ps2.product_id'),
                ));

            $rSelect = $this->getSql()->select()
                ->from(array('pt' => 'products_taste'))
                ->columns(array('coefficient' => new Expression('MIN(pt.coefficient)')))
                ->where(array(
                    't.id' => new Expression('pt.depend'),
                ));

            $select = clone $catalog->select()
                ->columns(array('id', 'name', 'discount', 'url',
                    'stock'        => $sSelect,
                    'coefficient'  => $rSelect
                ))
                ->join(array('pp' => 'products_size'), 't.id = pp.depend', array('price_base' => 'price', 'price_id' => 'id'), 'left')
                ->limit(3)
                ->order(new Expression('RAND()'))
                ->group('t.id');

            $catalog->select()
                ->join(array('pr' => 'products_recommended') , new Expression('pr.product_id = t.id AND pr.depend = ' . $model->getId()), []);

            if(!$catalog->count()) {
                $select
                    ->order('stock DESC')
                    ->order(new Expression('RAND()'))
                    ->limit(3)
                    ->where
                        ->notEqualTo('t.id', $model->getId())
                        ->equalTo('catalog_id', $model->get('catalog_id'));

                $catalog->setSelect($select);
            }

            return $catalog;
        });

        $this->addPlugin('size', function($model) {
            $props = new Size();
            $catalog = $props->getCollection()->getPlugin()->setParentId($model->getId());

            return $catalog;
        });

        $this->addPlugin('features', function($model) {
            $props = new Entity();
            $props->setTable('products_features');
            $props->addProperties(array(
                'depend'  => [],
                'name'    => [],
            ));

            $catalog = $props->getCollection()->getPlugin()->setParentId($model->getId());

            return $catalog;
        });

        $this->addPlugin('taste', function($model, $options) {
            $props = new Taste();

            $sSelect = $model->getSql()->select()
                ->from(array('ps' => 'products_stock'))
                ->columns(array('stock' => new Expression('IF(MAX(ps.count) >= 1,1,0)')))
                ->where(array(
                    new PredicateExpression('t.id = ps.taste_id'),
                    'ps.product_id' => $model->getId(),
                ));

            if($options['size_id']) {
                $sSelect->where(array(
                    'ps.size_id' => $options['size_id']
                ));
            }

            $catalog = $props->getCollection()->getPlugin()->setParentId($model->getId());
            $catalog->select()
                ->columns(array('id', 'name', 'coefficient', 'stock' => $sSelect))
                ->order('stock DESC')
                ->order('coefficient ASC')
                ->group('t.id');

            return $catalog;
        });

        $this->addPlugin('composition', function($model) {
            $props = new Entity();
            $props->setTable('products_composition');
            $props->addProperties(array(
                'depend'    => [],
                'name'      => [],
                'portion'   => [],
                'percent'   => [],
                'features'  => [],
                'type'      => [],
                'sort'      => [],
            ));

            $catalog = $props->getCollection()->getPlugin()->setParentId($model->getId());

            return $catalog;
        });

        $this->addPlugin('catalog', function($model) {
            $catalog = new Catalog();
            $catalog->setId($model->get('catalog_id'));

            return $catalog;
        }, array('independent' => true));

        $this->addPlugin('brand', function($model) {
            $brand = new Brands();
            $brand->setId($model->get('brand_id'));
            return $brand;
        }, array('independent' => true));

        $this->addPlugin('reviews', function($model) {
            $catalog = Reviews::getEntityCollection();
            $catalog->select()
                ->where(array(
                    'product_id' => $model->getId(),
                    'status'     => Reviews::STATUS_VERIFIED,
                ))
                ->order('time_create DESC');
            return $catalog;
        });

        $this->addPlugin('related', function($product) {
            $products = EntityFactory::collection(new Product());

            $prodId = $product->get('parent') ? $product->get('parent') : $product->getId();

            $adapter = $products->getDbAdapter();
            $sql = new Sql($adapter);

            $select = $sql->select()->from(array('t' => 'products'), array('url', 'color'));
            $select->where
                ->equalTo('id', $prodId)
                ->or
                ->equalTo('parent', $prodId);

            $products->setSelect($select);

            return $products;
        });

        $this->addPlugin('certificate', function() {
            $file = new \Aptero\Db\Plugin\File();
            $file->setTable('products_certificates');
            $file->setFolder('products_files');

            return $file;
        });

        $this->addPlugin('instruction', function() {
            $file = new \Aptero\Db\Plugin\File();
            $file->setTable('products_instructions');
            $file->setFolder('products_files');

            return $file;
        });

        $this->addPlugin('image', function($model) {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('products_images');
            $image->setFolder('products');
            $image->addResolutions(array(
                's' => array(
                    'width'  => 250,
                    'height' => 250,
                    'crop'   => true,
                ),
                'm' => array(
                    'width'  => 400,
                    'height' => 400,
                    'crop'   => true,
                ),
                'hr' => array(
                    'width'  => 800,
                    'height' => 800,
                )
            ));

            return $image;
        });

        $this->addPlugin('images', function() {
            $image = new ProductImages();
            $image->setTable('products_gallery');
            $image->setFolder('products_gallery');
            $image->addResolutions(array(
                's' => array(
                    'width'  => 250,
                    'height' => 250,
                    'crop'   => false,
                ),
                'm' => array(
                    'width'  => 400,
                    'height' => 400,
                    'crop'   => true,
                ),
                'hr' => array(
                    'width'  => 2000,
                    'height' => 2000,
                    'crop'   => true,
                )
            ));

            return $image;
        });
    }

    public function getUrl()
    {
        $url = '/goods/' . $this->get('url') . '/';

        if($this->get('size_id') && $this->get('taste_id')) {
            $url .= '?variation=' . $this->get('size_id') . '-' . $this->get('taste_id');
        }

        return $url;
    }

    public function getProp1Name()
    {
        return $this->get('prop_name_1') ? $this->get('prop_name_1') : 'Размер';
    }

    public function getProp2Name()
    {
        return $this->get('prop_name_2') ? $this->get('prop_name_2') : 'Вкус';
    }
}