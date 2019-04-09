<?php
namespace Catalog\Model;

use Application\Model\Content;
use Aptero\Db\Entity\EntityFactory;
use Aptero\Db\Entity\EntityHierarchy;

use Zend\Session\Container as SessionContainer;

class Catalog extends EntityHierarchy
{
    public function __construct()
    {
        $this->setTable('catalog');

        $this->addProperties([
            'parent'      => [],
            'name'        => [],
            'url'         => [],
            'url_path'    => [],
            'text'        => [],
            'header'      => [],
            'title'       => [],
            'description' => [],
            'keywords'    => [],
            'market_category'    => [],
        ]);

        $this->addPropertyFilterOut('header', function($model, $header) {
            return $header ? $header : $model->get('name');
        });

        $this->addPlugin('types', function($model) {
            $types = CatalogTypes::getEntityCollection();
            $types->select()
                ->columns(['id', 'name', 'short_name', 'url', 'depend'])
                ->order('sort')
                ->where(['depend' => $model->getId()]);

            return $types;
        });

        $this->addPlugin('products', function($category) {
            $products = Product::getEntityCollection();
            $products->select()->where(array('catalog_id' => $category->getId()));

            return $products;
        });

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('catalog_images');
            $image->setFolder('catalog');
            $image->addResolutions(array(
                's' => array(
                    'width'  => 80,
                    'height' => 70,
                ),
                'hr' => array(
                    'width'  => 1000,
                    'height' => 800,
                )
            ));

            return $image;
        });

        $this->addPlugin('props', function() {
            $props = new \Aptero\Db\Plugin\Properties();
            $props->setTable('catalog_properties');

            return $props;
        });

        $this->addPlugin('units', function() {
            $props = new \Aptero\Db\Plugin\Properties();
            $props->setTable('catalog_units');

            return $props;
        });

        $this->addPlugin('content', function($model) {
            $content = Content::getEntityCollection();
            $content->select()
                ->where(array('depend' => $model->getId()))
                ->order('t.sort');

            return $content;
        });

        $this->select()->where(array('active' => 1));
    }

    public function getCatalogIds($category = null)
    {
        if(!$category) {
            $category = $this;
        }

        $ids = array($category->getId());

        $children = $category->getChildren();

        if($children->count()) {
            foreach($children as $child) {
                $ids = array_merge($ids, $this->getCatalogIds($child));
            }
        }

        return $ids;
    }

    public function getBrands()
    {
        $brands = EntityFactory::collection(new Brands());

        $brands->select()
            ->columns(array('id', 'name', 'url'))
            ->quantifier('DISTINCT')
            ->join(array('p' => 'products'), 'p.brand_id = t.id', [])
            ->where(array('p.catalog_id' => $this->getCatalogIds()))
            ->where(array('p.active' => 1))
            ->where->greaterThanOrEqualTo('price', 1);

        return $brands;
    }

    public function getUrl()
    {
        return '/catalog/' . $this->get('url_path') . '/';
    }

    public static function getEntityCollection()
    {
        $catalog = parent::getEntityCollection();

        $catalog->select()->where(array('active' => 1));

        return $catalog;
    }
}