<?php
namespace Catalog\Model;

use Application\Model\Module;
use Aptero\Db\Entity\Entity;
use Aptero\Db\Entity\EntityFactory;

class ProductRequest extends Entity
{
    public function __construct()
    {
        $this->setTable('orders_request');

        $this->addProperties(array(
            'product_id'  => array(),
            'price_id'    => array(),
            'taste_id'    => array(),
            'contact'     => array(),
            'time_create' => array(),
        ));

        $this->addPlugin('product', function($model) {
            $product = new Product();
            $product->addProperty('taste');
            $product->addProperty('size');

            $product->setId($model->get('product_id'));
            $product->select()
                ->join(['pp' => 'products_size'], 't.id = pp.depend', ['price_base' => 'price', 'size' => 'name', 'weight' => 'weight'])
                ->join(['pt' => 'products_taste'], 't.id = pt.depend', ['coefficient', 'taste' => 'name'])
                ->where([
                    'pp.id' => $model->get('price_id'),
                    'pt.id' => $model->get('taste_id'),
                ]);
            return $product;
        }, array('independent' => true));
    }
}