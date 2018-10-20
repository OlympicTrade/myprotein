<?php
namespace Catalog\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Delivery\Model\Delivery;

class CartList extends AbstractHelper
{
    public function __invoke($cart, $price)
    {
        if(!$cart || !$cart->count()) {
            return '<div class="empty">Ваша корзина пуста</div>';
        }

        $view = $this->getView();

        $html =
            '<div class="cart-list">'
                .'<div class="order-info top">'
                    .'<div class="sum">Полная стоимость: <span class="cart-price">' . $price . '</span> <i class="fas fa-ruble-sign"></i></div>'
                    .'<a href="/order/" class="btn order orange order-popup">Оформить заказ</a>'
                .'</div>'
                .'<div class="list">';

        foreach($cart as $cartRow) {
            $product = $cartRow->getPlugin('product');
            $url = '/goods/' . $product->get('url') . '/';

            $html .=
                '<div class="product" data-list="Cart" data-id="' . $product->getId() . '" data-product_id="' . $product->getId() . '" data-size_id="' . $cartRow->get('size_id') . '" data-taste_id="' . $cartRow->get('taste_id') . '">'
                    .'<a href="' . $url . '" class="pic pr-link">'
                        .'<img src="' . $product->getPlugin('image')->getImage('s') . '" alt="' . $product->get('name') . '">'
                    .'</a>'
                    .'<div class="info">'
                        .'<a href="' . $url . '" class="name pr-link">' . $product->get('name') . '</a>'
                        .'<div class="props">'
                            .'<div class="row">'
                                .'<div class="label">Вкус:</div>'
                                . $cartRow->get('taste')
                            .'</div>'
                            .'<div class="row">'
                                .'<div class="label">Размер:</div>'
                                . $cartRow->get('size')
                            .'</div>'
                        .'</div>'
                    .'</div>'
                    .'<div class="price-box">'
                        .'<div class="sum"><span>' . $view->price($product->get('price') * $cartRow->get('count')) . '</span> <i class="fas fa-ruble-sign"></i></div>'
                        .'<div class="std-counter s">'
                            .'<div class="incr"></div>'
                            .'<input class="js-cart-count" value="' . $cartRow->get('count') . '" min="1" max="999">'
                            .'<div class="decr"></div>'
                        .'</div>'
                        .'<div class="per-unit"><b>' . $view->price($product->get('price')) . '</b> <i class="fas fa-ruble-sign"></i> за шт.</div>'
                    .'</div>'
                    .'<span class="close js-cart-del"></span>'
                .'</div>';
        }
		
        $html .=
				'</div>'
                .'<div class="order-info">'
                    .'<div class="sum">Полная стоимость: <span class="cart-price">' . $price . '</span> <i class="fas fa-ruble-sign"></i></div>'
                    .'<a href="/order/" class="btn order orange order-popup">Оформить заказ</a>'
                .'</div>'
				.$view->deliveryNotice($price)
			.'</div>';
			
        return $html;
    }
}