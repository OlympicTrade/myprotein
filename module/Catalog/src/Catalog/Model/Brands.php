<?php
namespace Catalog\Model;

use Aptero\Db\Entity\Entity;
use Aptero\Db\Entity\EntityFactory;
use CatalogAdmin\Model\Plugin\ProductProps;
use Catalog\Model\Catalog;
use Aptero\Db\Plugin\Attributes;
use Zend\Db\Sql\Sql;

class Brands extends Entity
{
    public function __construct()
    {
        $this->setTable('products_brands');

        $this->addProperties(array(
            'url'       => array(),
            'name'      => array(),
            'text'      => array(),
        ));

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('products_brands_images');
            $image->setFolder('brands');
            $image->addResolutions(array(
                's' => array(
                    'width'  => 123,
                    'height' => 70,
                    'crop'   => true,
                ),
            ));

            return $image;
        });
    }
}