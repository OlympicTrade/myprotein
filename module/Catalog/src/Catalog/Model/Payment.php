<?php
namespace Catalog\Model;

use Aptero\Db\Entity\Entity;

class Payment extends Entity
{
    public function __construct()
    {
        $this->setTable('products_brands');

        $this->addProperties(array(
            'amount'    => array(),
            'name'      => array(),
            'type'      => array(),
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