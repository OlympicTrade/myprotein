<?php

namespace Catalog\Service;

use Application\Model\Module;
use Aptero\Db\Entity\EntityFactory;
use Aptero\Service\AbstractService;
use Catalog\Model\Cart;
use Catalog\Model\Order;
use Catalog\Model\Products;
use Delivery\Model\Delivery;
use Zend\Json\Decoder as JsonDecoder;

class CartService extends AbstractService
{
    /**
     * @var \Aptero\Db\Entity\EntityCollection
     */
    protected $cart = array();

    protected function generateCartKey($data)
    {
        return $data->product_id . '-' . $data->taste_id . '-' . $data->size_id;
    }

    /**
     * @return \Aptero\Db\Entity\EntityCollection
     */
    public function getCookieCart()
    {
        if($this->cart) {
            return $this->cart;
        }

        if(!isset($_COOKIE['cart'])) {
            return array();
        }

        try {
            $cookie = JsonDecoder::decode($_COOKIE['cart']);
        } catch (\Zend\Json\Exception\RuntimeException $exception) {
            return array();
        }

        if(!is_array($cookie)) {
            return array();
        }

        $cookieCart = array();
        foreach($cookie as $cProduct) {
            if(!($productId = (int) $cProduct->product_id) || !((int) $cProduct->count)) {
                continue;
            }

            $cookieCart[$this->generateCartKey($cProduct)] = $cProduct;
        }

        if(empty($cookieCart)) {
            return array();
        }

        $cart = new Cart();

        $cart->addProperties(array(
            'size_id'    => ['virtual' => true],
            'taste_id'   => ['virtual' => true],
            'taste'      => ['virtual' => true],
            'size'       => ['virtual' => true],
            'weight'     => ['virtual' => true],
        ));

        $cart = $cart->getCollection();

        $cart->select()
            ->from(['p' => 'products'])
            ->columns([
                'product_id'        => 'id',
                'product-id'        => 'id',
                'product-url'       => 'url',
                'product-name'      => 'name',
                'product-discount'  => 'discount',
            ])
            ->join(['pp' => 'products_size'], 'p.id = pp.depend', ['size_id' => 'id', 'size' => 'name', 'weight' => 'weight', 'product-price_base' => 'price'])
            ->join(['pt' => 'products_taste'], 'p.id = pt.depend', ['taste_id' => 'id', 'taste' => 'name', 'product-coefficient' => 'coefficient'])
            ->join(['ps' => 'products_stock'], 'p.id = ps.product_id AND pt.id = ps.taste_id AND pp.id = ps.size_id', [], 'left')
            ->where->greaterThanOrEqualTo('ps.count', 1);

        $i = 0;
        $where = '(';
        foreach($cookieCart as $id => $row) {
            $i++;
            $where .= ($i == 1 ? '' : ' OR ') . '(p.id = ' . (int) $row->product_id . ' AND pp.id=' . (int) $row->size_id . ' AND pt.id=' . (int) $row->taste_id . ')';
        }
        $where .= ')';

        $cart->select()->where($where);

        foreach($cart as $row) {
            $product = $row->getPlugin('product');
            $count = $cookieCart[$this->generateCartKey($row)]->count;
            $row->set('count', $count);
            $row->set('count', $count);
            $price =  $product->get('price');
            $row->set('price', $price);
        }

        $this->cart = $cart;
        return $this->cart;
    }

    public function getCustomCart($product, $count)
    {
        $cart = EntityFactory::collection(new Cart());

        $cart->select()
            ->from(array('p' => 'products'))
            ->columns(array('product-id' => 'id'))
            ->where(array('p.id'   => $product->getId()));

        foreach($cart as $row) {
            $row->set('count', $count);
            $row->set('price', $product->get('price'));
            $row->set('product_id', $product->getId());
        }

        $this->cart = $cart;
        return $this->cart;
    }

    public function getCartPrice($cart = null)
    {
        if(!$cart) $cart = $this->getCookieCart();

        $price = 0;

        foreach($cart as $product) {
            $price += $product->get('price') * $product->get('count');
        }

        return $price;
    }

    public function getCartWeight($cart = null)
    {
        if(!$cart) $cart = $this->getCookieCart();

        $weight = 0;

        foreach($cart as $product) {
            $weight += $product->get('weight') * $product->get('count');
        }

        return $weight;
    }

    public function checkInCart($product)
    {
        foreach($this->getCookieCart() as $cProduct) {
            if(
                $cProduct->get('product_id') == $product->getId() &&
                $cProduct->get('taste_id') == $product->getId('taste_id') &&
                $cProduct->get('size_id') == $product->getId('size_id')
            ) {
                return true;
            }
        }

        return false;
    }

    public function getDeliveryInfo($price = null, $delivery = null)
    {
        if(!$delivery) {
            $delivery = Delivery::getInstance();
        }

        if($price === null) {
            $price = $this->getCartPrice();
        }

        $result = [
            'pickup'         => 0,
            'courier_outgo'  => 0,
            'courier'        => 0,
            'pickup_outgo'  => 0,
        ];

        if($delivery->get('courier_free') > $price) {
            $result['courier'] = $delivery->get('courier_price');
        } else {
            $result['courier_outgo'] = $delivery->get('courier_price');
        }

        if($delivery->get('pickup_free') > $price) {
            $result['pickup'] = $delivery->get('pickup_price');
        } else {
            $result['pickup_outgo'] = $delivery->get('pickup_price');
        }

        return $result;
    }

    public function getCartInfo()
    {
        $cart = $this->getCookieCart();

        $cartInfo = [];

        $count = 0;

        foreach($cart as $product) {
            $cartInfo['cart'][] = array(
                'count'     => $product->get('count'),
                'size_id'   => $product->get('size_id'),
                'taste_id'  => $product->get('taste_id'),
                'product_id'=> $product->get('product_id'),
                'price'     => $product->get('price'),
            );

            $count += $product->get('count');
        }

        $price = $this->getCartPrice($cart);

        $cartInfo['price'] = $this->getCartPrice($cart);
        $cartInfo['weight'] = $this->getCartWeight($cart);
        $cartInfo['count'] = $count;
        $cartInfo['delivery'] = $this->getDeliveryInfo($price);

        return $cartInfo;
    }

    /**
     * @return \Catalog\Service\ProductsService
     */
    protected function getProductsService()
    {
        return $this->getServiceManager()->get('Catalog\Service\ProductsService');
    }
}