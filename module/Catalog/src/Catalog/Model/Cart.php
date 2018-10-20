<?php
namespace Catalog\Model;

use Aptero\Db\Entity\Entity;

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
            'count'       => array(), //Сколько реально доступно со склада
            'order_count' => array(), //Сколько товаров заказал клиент
            'price'       => array(),
            'bonus'       => array(),
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
                    'pp.id' => $model->get('size_id'),
                    'pt.id' => $model->get('taste_id'),
                ]);
            return $product;
        }, array('independent' => true));


        $this->addPlugin('size', function($model) {
            $price = new Size();
            $price->select()->where(array('id' => $model->get('size_id')));

            return $price;
        }, array('independent' => true));

        $this->addPlugin('taste', function($model) {
            $taste = new Taste();
            $taste->select()->where(array('id' => $model->get('taste_id')));

            return $taste;
        }, array('independent' => true));
    }

    public function getProductVariant()
    {
        return trim($this->getPlugin('size')->get('name') . ' - ' . $this->getPlugin('taste')->get('name'),
            '- ');
    }
}