<?php
namespace CatalogAdmin\Model;

use Aptero\Db\Entity\Entity;
use Aptero\Db\Entity\EntityHierarchy;
use CatalogAdmin\Model\Plugin\ProductImages;
use ManagerAdmin\Model\Task;

class Products extends Entity
{
    public function __construct()
    {
        $this->setTable('products');

        $this->addProperties([
            'parent'        => [],
            'desc'          => [],
            'name'          => [],
            'preview'       => [],
            'url'           => [],
            'mp_url'        => [],
            'catalog_id'    => [],
            'brand_id'      => [],
            'text'          => [],
            'discount'      => [],
            'margin'        => [],
            'video'         => [],
            'title'         => [],
            'description'   => [],
            'ya_market'     => [],
            'go_merchant'   => [],
            'olympic_id'    => [],

            'tags'          => [],
            'color'         => [],
            'price'         => [],
            'price_opt'     => [],
            'vendor'        => [],
            'count'         => [],
            'event'         => [],
            'visible'       => [],
            'time_update'   => [],
            'sort'          => [],
            'popularity'    => [],

            'prop_name_1'   => [],
            'prop_name_2'   => [],

            'price_base' => ['virtual' => true],
            'coefficient' => ['virtual' => true],
        ]);

        /*$this->addPlugin('tags', function($model) {
            $item = new Entity();
            $item->setTable('products_tags');
            $item->addProperties(array(
                'depend'    => [],
                'name'      => [],
            ));
            $catalog = $item->getCollection()->getPlugin();
            $catalog->setParentId($model->getId());

            return $catalog;
        });*/

        $this->addPlugin('types', function($model) {
            $item = new Entity();
            $item->setTable('products_types');
            $item->addProperties(array(
                'depend'    => [],
                'type_id'   => [],
            ));
            $catalog = $item->getCollection()->getPlugin();
            $catalog->setParentId($model->getId());

            return $catalog;
        });

        $this->addPropertyFilterOut('price', function ($model) {
            $price = $model->get('price_base') * $model->get('coefficient');
            $price -= $price * ($model->get('discount') / 100);

            return ceil($price / 10) * 10;
        });

        $this->addPlugin('recommended', function ($model) {
            $item = new Entity();
            $item->setTable('products_recommended');
            $item->addProperties(array(
                'depend' => [],
                'product_id' => [],
            ));
            $catalog = $item->getCollection()->getPlugin();
            $catalog->setParentId($model->getId());

            return $catalog;
        });

        $this->addPlugin('articles', function ($model) {
            $item = new Entity();
            $item->setTable('products_articles');
            $item->addProperties([
                'depend' => [],
                'article_id' => [],
            ]);
            $catalog = $item->getCollection()->getPlugin();
            $catalog->setParentId($model->getId());

            return $catalog;
        });

        $this->addPlugin('size', function ($model) {
            $props = new Size();

            $catalog = $props->getCollection()->getPlugin();
            $catalog->setParentId($model->getId());

            return $catalog;
        });

        $this->addPlugin('features', function ($model) {
            $props = new Entity();
            $props->setTable('products_features');
            $props->addProperties(array(
                'depend' => [],
                'name' => [],
            ));

            $catalog = $props->getCollection()->getPlugin();
            $catalog->setParentId($model->getId());

            return $catalog;
        });

        $this->addPlugin('taste', function ($model) {
            $props = new Taste();

            $catalog = $props->getCollection()->getPlugin();
            $catalog->setParentId($model->getId());

            return $catalog;
        });

        $this->addPlugin('composition', function ($model) {
            $props = new Entity();
            $props->setTable('products_composition');
            $props->addProperties(array(
                'depend' => [],
                'name' => [],
                'portion' => [],
                'percent' => [],
                'features' => [],
                'type' => [],
                'sort' => [],
            ));

            $catalog = $props->getCollection()->getPlugin()->setParentId($model->getId());

            return $catalog;
        });

        $this->addPlugin('catalog', function ($model) {
            $catalog = new \CatalogAdmin\Model\Catalog();
            $catalog->setId($model->get('catalog_id'));

            return $catalog;
        }, array('independent' => true));

        $this->addPlugin('brand', function ($model) {
            $catalog = new \CatalogAdmin\Model\Brands();
            $catalog->setId($model->get('brand_id'));

            return $catalog;
        }, array('independent' => true));

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('products_images');
            $image->setFolder('products');
            $image->addResolutions(array(
                'a' => array(
                    'width'  => 162,
                    'height' => 162,
                ),
                'hr' => array(
                    'width'  => 1000,
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
                'a' => array(
                    'width'  => 162,
                    'height' => 162,
                ),
                'hr' => array(
                    'width'  => 1000,
                    'height' => 800,
                )
            ));

            $image->select()->order('sort');

            return $image;
        });

        $this->addPlugin('certificate', function () {
            $file = new \Aptero\Db\Plugin\File();
            $file->setTable('products_certificates');
            $file->setFolder('products_files');

            return $file;
        });

        $this->addPlugin('instruction', function () {
            $file = new \Aptero\Db\Plugin\File();
            $file->setTable('products_instructions');
            $file->setFolder('products_files');

            return $file;
        });

        $this->getEventManager()->attach(array(Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE), function ($event) {
            $model = $event->getTarget();

            if (!$model->get('url')) {
                $model->set('url', \Aptero\String\Translit::url($model->get('name')));
            }

            return true;
        });

        $this->addPlugin('attrs', function() {
            $properties = new \Aptero\Db\Plugin\Attributes();
            $properties->setTable('products_attrs');

            return $properties;
        });

        $this->getEventManager()->attach(array(Entity::EVENT_POST_INSERT), function ($event) {
            $model = $event->getTarget();

            (new Task())->setVariables([
                'task_id'       => Task::TYPE_PRODUCT_NEW,
                'item_id'       => $model->getId(),
                'name'          => 'Добавление товара',
                'duration'      => 20,
            ])->save();

            return true;
        });

        $this->getEventManager()->attach(array(Entity::EVENT_PRE_DELETE), function ($event) {
            $model = $event->getTarget();

            $stock = new Stock();
            $stock->select()->where(array('product_id' => $model->getId()));
            $stock->remove();

            return true;
        });
    }

    public function getUrl()
    {
        return '/goods/' . $this->get('url') . '/';
    }

    public function getEditUrl()
    {
        return '/admin/catalog/products/edit/?id=' . $this->getId();
    }

    public function getProp1Name()
    {
        return $this->get('prop_name_1') ? $this->get('prop_name_1') : 'Размер';
    }

    public function getProp2Name()
    {
        return $this->get('prop_name_2') ? $this->get('prop_name_2') : 'Вкус';
    }

    static public function syncOlympic($productId)
    {
        //file_get_contents('https://olympic-torch.ru/sync/update-product/?id=' . $productId);
    }
}