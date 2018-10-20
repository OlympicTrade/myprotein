<?php
namespace DiscountsAdmin\Model;

use Aptero\Db\Entity\Entity;
use Zend\Session\Container as SessionContainer;

class Discount extends Entity
{
    public function __construct()
    {
        $this->setTable('discounts');

        $this->addProperties(array(
            'name'        => array(),
            'discount'    => array(),
            'color'       => array('default' => '#000000'),
            'background'  => array('default' => '#ffffff'),
            'border'      => array('default' => '#000000'),
            'shape'       => array('default' => 'square'),
            'date_from'   => array(),
            'date_to'     => array(),
        ));

        $this->addPlugin('products', function($model) {
            $item = new Entity();
            $item->setTable('discounts_products');
            $item->addProperties(array(
                'depend'     => array(),
                'product_id' => array(),
                'discount'   => array(),
            ));
            $catalog = $item->getCollection()->getPlugin();
            $catalog->setParentId($model->getId());

            return $catalog;
        });

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('discounts_images');
            $image->setFolder('discounts');
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
    }
}