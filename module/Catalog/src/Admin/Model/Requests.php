<?php
namespace CatalogAdmin\Model;

use Aptero\Db\Entity\Entity;

class Requests extends Entity
{
    const STATUS_NEW      = 0;
    const STATUS_COMPLETE = 1;
    const STATUS_REJECT   = 2;

    static public $statuses = [
        self::STATUS_NEW        => 'Новая заявка',
        self::STATUS_COMPLETE   => 'Выполнена',
        self::STATUS_REJECT     => 'Отклонена',
    ];

    public function __construct()
    {
        $this->setTable('orders_request');

        $this->addProperties([
            'product_id'  => [],
            'price_id'    => [],
            'taste_id'    => [],
            'contact'     => [],
            'status'      => [],
            'time_create' => [],
        ]);

        $this->addPlugin('size', function($model) {
            $price = new Size();
            $price->select()->where(array('id' => $model->get('price_id')));

            return $price;
        }, array('independent' => true));

        $this->addPlugin('taste', function($model) {
            $taste = new Taste();
            $taste->select()->where(array('id' => $model->get('taste_id')));

            return $taste;
        }, array('independent' => true));

        $this->addPlugin('product', function($model) {
            $product = new Products();
            $product->addProperty('taste');
            $product->addProperty('size');

            $product->setId($model->get('product_id'));
            $product->select()
                ->join(array('pp' => 'products_size'), 't.id = pp.depend', array('price_base' => 'price', 'size' => 'name'))
                ->join(array('pt' => 'products_taste'), 't.id = pt.depend', array('coefficient', 'taste' => 'name'))
                ->where(array(
                    'pt.id' => $model->get('taste_id'),
                ));
                
            if($model->get('price_id')) {
            	$product->select()->where([
                    'pp.id' => $model->get('price_id'),
        		]);
            }
            
            return $product;
        }, array('independent' => true));
    }

    public function getEditUrl()
    {
        return '/admin/catalog/requests/edit/?id=' . $this->getId();
    }
}