<?php
namespace CatalogAdmin\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Form\ElementInterface;

class CartList extends AbstractHelper
{
    protected $options = [
        'type' => 'short'
    ];

    public function __invoke($order, $options = [])
    {
        $this->options = $options + $this->options;

        $html =
            '<table class="std-table cart-table">'
            .'<thead>'
            .'<tr>'
                .'<th style="width: 75px" class="ta-c">Кол-во</th>'
                .'<th style="width: 280px">Товар/Услуга</th>'
                .'<th style="width: 150px">Вкус</th>';

        if ($this->options['type'] == 'full') {
            $html .=
                '<th style="width: 160px">Стоимость</th>'
                .'<th style="width: 100px" class="ta-c">На складе</th>'
                .'<th style="width: 100px" class="ta-c">В Заказе</th>'
                .'<th style="width: 100px" class="ta-c">Кол-во</th>';
        }

        $html .=
                '<th class="ta-r">Сумма</th>'
            .'</tr>'
            .'</thead>'
            .'<tbody>';

        $cart = clone $order->getPlugin('cart');
        $cart->clear();
        $cart->select()->order('count DESC');

        foreach($cart as $row) {
            $html .= $this->cartRow($row);
        }

        $profit = $order->get('income') + $order->get('delivery_income') - $order->get('outgo') - $order->get('delivery_outgo');

        if ($this->options['type'] == 'full') {
            $html .=
                '</tbody>'
                .'<tfoot>'
                    .'<tr>'
                        .'<td colspan="5" rowspan="2">'
                            .'<div class="product-form">'
                                .'<div class="form">'
                                .'<div class="row">'
                                    .'<input type="text" class="std-text product" placeholder="Название товара">'
                                    .'<input type="hidden" data-name="id">'
                                .'</div>'
                                .'<div class="props row"></div>'
                                    .'<span class="btn">Добавить в корзину</span>'
                                .'</div>'
                            .'</div>'
                        .'</td>'
                        .'<td colspan="3" class="ta-r">'
                            .'<div>Полная стоимость: <b> <span class="order-price">' . $this->getView()->price($order->get('income')) . '</span> руб.</b></div>'
                            .'<div>Чистая прибыль: <b> <span class="order-profit">' . $this->getView()->price($profit) . '</span> руб.</b></div>'
                        .'</td>'
                    .'</tr>'
                .'</tfoot>'
            .'</table>';
        } else {
            $html .=
                '</tbody>'
                .'<tfoot>'
                    .'<tr>'
                        .'<td colspan="4" class="ta-r">'
                            .'<a href="/admin/catalog/orders/edit/?id=' . $order->getId() . '" class="edit btn">Подробнее</a>'
                            .'<div>Полная стоимость: <b> <span class="order-price">' . $this->getView()->price($order->get('income')) . '</span> руб.</b></div>'
                            .'<div>Чистая прибыль: <b> <span class="order-profit">' . $this->getView()->price($profit) . '</span> руб.</b></div>'
                        .'</td>'
                    .'</tr>'
                .'</tfoot>'
                .'</table>';
        }
        
        return $html;
    }
    
    public function cartRow($cartRow)
    {
        $view = $this->getView();

        $productUrl = $view->url('admin', array('module' => 'catalog', 'section' => 'products', 'action' => 'edit')) . '?id=' . $cartRow->getPlugin('product')->getId();

        $count = $cartRow->get('count');
        $stock = $cartRow->getPlugin('stock')->get('count');

        if($count > 1) {
            $countStr = '<b style="color: red">' . $count . ' шт. </b>';
        } else {
            $countStr = $count . ' шт. ';
        }

        $taste = $cartRow->getPlugin('taste')->get('name');
        if(!in_array($taste, array('', 'Без вкуса', 'Натуральный вкус'))) {
            $tasteStr = '<b style="color: red">' . $taste . '</b>';
        } else {
            $tasteStr = $taste;
        }

        $productName = $cartRow->getPlugin('product')->get('name');

        $size = $cartRow->getPlugin('size')->get('name');
        if($size) {
            $productName .= ' <span style="color: #000">(' . $cartRow->getPlugin('size')->get('name') . ')</span>';
        }

        $html =
            '<tr>'
                .'<td class="sum ta-c">' . $countStr . '</td>'
                .'<td class="product"><a href="' . $productUrl . '">' . $productName . '</a></td>'
                .'<td>' . $tasteStr . '</td>';

        if ($this->options['type'] == 'full') {
            $html .=
                '<td>' . $view->price($cartRow->get('price')) . ' руб.</td>'
                .'<td class="ta-c pr-stock"><a href="' . $productUrl . '#edit-tabs=stock" target="_blank"><span>' . $cartRow->getPlugin('stock')->get('count') . '</span> шт</a></td>'
                .'<td class="ta-c">' . ((int)$cartRow->get('order_count')) . ' шт</td>'
                .'<td>'
                    .'<div class="std-counter s">'
                        .'<div class="incr"></div>'
                        .'<input value="' . $count . '" min="0" max="' . ($count + $stock) . '" name="count" class="cart-count" data-id="' . $cartRow->getId() . '">'
                        .'<div class="decr"></div>'
                    .'</div>'
                .'</td>';
        }
        $html .=
                '<td class="sum ta-r pr-price"><span>' . $view->price($cartRow->get('price') * $count) . '</span> руб.</td>'
            .'</tr>';

        return $html;
    }
}