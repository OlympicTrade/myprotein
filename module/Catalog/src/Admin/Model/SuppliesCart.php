<?php
namespace CatalogAdmin\Model;

use Aptero\Db\Entity\Entity;
use Aptero\Db\Entity\EntityHierarchy;

class SuppliesCart extends Entity
{
    public function __construct()
    {
        $this->setTable('supplies_products');

        $this->addProperties(array(
            'supply_id'   => [],
            'product_id'  => [],
            'size_id'     => [],
            'taste_id'    => [],
            'count'       => [],
            'order_count' => [],
            'price'       => [],
        ));

        $this->addPlugin('product', function($model) {
            $product = new Products();
            $product->addProperty('taste');
            $product->addProperty('size');

            $product->setId($model->get('product_id'));
            $product->select()
                ->join(array('pp' => 'products_size'), 't.id = pp.depend', array('price_base' => 'price', 'size' => 'name'))
                ->join(array('pt' => 'products_taste'), 't.id = pt.depend', array('coefficient', 'taste' => 'name'))
                ->where(array(
                    'pp.id' => $model->get('size_id'),
                    'pt.id' => $model->get('taste_id'),
                ));
            return $product;
        }, array('independent' => true));

        $this->addPlugin('size', function($model) {
            $price = new Size();
            $price->select()->where(['id' => $model->get('size_id')]);

            return $price;
        }, array('independent' => true));

        $this->addPlugin('taste', function($model) {
            $taste = new Taste();
            $taste->select()->where(['id' => $model->get('taste_id')]);

            return $taste;
        }, array('independent' => true));

        $this->addPlugin('stock', function($model) {
            $stock = new Stock();
            $stock->select()->where([
                'product_id' => $model->get('product_id'),
                'taste_id'   => $model->get('taste_id'),
                'size_id'    => $model->get('size_id'),
            ]);

            return $stock;
        }, ['independent' => true]);
    }
}