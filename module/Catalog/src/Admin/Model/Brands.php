<?php
namespace CatalogAdmin\Model;

use Aptero\Db\Entity\Entity;
use Aptero\Db\Entity\EntityHierarchy;

class Brands extends Entity
{
    public function __construct()
    {
        $this->setTable('products_brands');

        $this->addProperties(array(
            'name'        => array(),
            'url'         => array(),
            'text'        => array(),
        ));

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('products_brands_images');
            $image->setFolder('brands');
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

        $this->getEventManager()->attach(array(Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE), function ($event) {
            $model = $event->getTarget();

            if(!$model->get('url')) {
                $model->set('url', \Aptero\String\Translit::url($model->get('name')));
            }

            return true;
        });
    }
}