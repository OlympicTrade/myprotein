<?php
namespace CatalogAdmin\Model;

use Aptero\Db\Entity\Entity;
use Aptero\Db\Entity\EntityHierarchy;
use Zend\Session\Container as SessionContainer;

class Catalog extends EntityHierarchy
{
    public function __construct()
    {
        $this->setTable('catalog');

        $this->addProperties(array(
            'name'          => [],
            'url'           => [],
            'url_path'      => [],
            'header'        => [],
            'title'         => [],
            'text'          => [],
            'description'   => [],
            'keywords'      => [],
            'parent'        => [],
            'ya_market_id'  => [],
            'time_update'   => [],
            'active'        => [],
            'sort'          => [],
        ));

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('catalog_images');
            $image->setFolder('catalog');
            $image->addResolutions(array(
                'a' => array(
                    'width'  => 120,
                    'height' => 120,
                ),
                'hr' => array(
                    'width'  => 1000,
                    'height' => 800,
                )
            ));

            return $image;
        });

        $this->addPlugin('types', function($model) {
            $props = new CatalogTypes();
            $catalog = $props->getCollection()->getPlugin()->setParentId($model->getId());
            return $catalog;
        });

        //URL
        $this->getEventManager()->attach(array(Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE), function ($event) {
            $model = $event->getTarget();

            if(!$model->get('url')) {
                $model->set('url', \Aptero\String\Translit::url($model->get('name')));
            }

            return true;
        });

        //URL Path
        $this->getEventManager()->attach(array(Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE), function ($event) {
            $model = $event->getTarget();

            $model->setParentId($model->get('parent'));
            $parent = $model->getParent();

            $url_path = $model->get('url');
            while($parent) {
                $url_path = $parent->get('url') . '/' . $url_path;
                $parent = $parent->getParent();
            }

            $model->set('url_path', $url_path);

            return true;
        });
    }
}