<?php
namespace Discounts\Model;

use Aptero\Db\Entity\Entity;
use Catalog\Model\Product;
use Zend\Db\Sql\Expression;

class Discount extends Entity
{
    public function __construct()
    {
        $this->setTable('discounts');

        $this->addProperties(array(
            'name'        => array(),
            'discount'    => array(),
            'color'       => array('default' => '000000'),
            'background'  => array('default' => 'ffffff'),
            'border'      => array('default' => '000000'),
            'shape'       => array('default' => 'square'),
            'date_from'   => array(),
            'date_to'     => array(),
        ));

        $this->addPlugin('products', function($model) {
            $catalog = Product::getEntityCollection();
            $catalog->getPrototype()->addProperty('discount_new', array('virtual' => true));
            $catalog->select()
                ->join(array('dp' => 'discounts_products') , new Expression('dp.product_id = t.id AND dp.depend = ' . $model->getId()), array('discount_new' => 'discount'));

            return $catalog;
        });

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('discounts_images');
            $image->setFolder('discounts');
            $image->addResolutions(array(
                'm' => array(
                    'width'  => 1920,
                    'height' => 544,
                ),
            ));

            return $image;
        });
    }
}