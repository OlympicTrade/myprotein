<?php
namespace Catalog\View\Helper;

use Zend\View\Helper\AbstractHelper;

class CartWidget extends AbstractHelper
{
    protected $cart = null;

    public function __construct($cart)
    {
        $this->cart = $cart;
    }
    public function __invoke()
    {
        $html =
            '<div class="cart cart-summary">'
                .'<a href="' . $this->getView()->url('cart') . '">Корзина</a>'
                .'<div class="cart">'
                    .'<div>Кол-во: <span class="count">' . $this->cart['count'] . '</span></div>'
                    .'<div>Сумма: <span class="price">' . $this->getView()->price($this->cart['price']) . '</span></div>'
                .'</div>'
            .'</div>';

        return $html;
    }
}