<?php
namespace CatalogAdmin\Model;

use Aptero\Db\Entity\Entity;
use Zend\Db\Sql\Expression;

class Cart extends Entity
{
    public function __construct()
    {
        $this->setTable('orders_cart');

        $this->addProperties(array(
            'order_id'    => array(),
            'product_id'  => array(),
            'size_id'     => array(),
            'taste_id'    => array(),
            'count'       => array(),
            'order_count' => array(),
            'price'       => array(),
        ));

        $this->addPlugin('product', function($model) {
            $product = new Products();
            $product->addProperty('taste');
            $product->addProperty('size');

            $product->setId($model->get('product_id'));
            $product->select()
                ->join(['pp' => 'products_size'],  new Expression('t.id = pp.depend AND pp.id = ' . $model->get('size_id')), ['price_base' => 'price', 'size' => 'name'], 'left')
                ->join(['pt' => 'products_taste'], new Expression('t.id = pt.depend AND pt.id = ' . $model->get('taste_id')), ['coefficient', 'taste' => 'name'], 'left');
            return $product;
        }, ['independent' => true]);

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

    public function serializeArray($result = array(), $prefix = '')
    {
        //$this->load();

        $result[$prefix . 'id'] = $this->getId();
        foreach($this->properties as $key => $val) {
            $result[$prefix . $key] = $val['value'];
        }

        foreach(array_keys($this->plugins) as $name) {
            $plugin = $this->getPlugin($name);
            $result = $plugin->serializeArray($result, $prefix . $name . '-');
        }

        return $result;
    }
}