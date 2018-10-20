<?php
namespace CatalogAdmin\View\Helper;

use Zend\View\Helper\AbstractHelper;

class SupplyCart extends AbstractHelper
{
    public function __invoke($supply)
    {
        $cart = $supply->getPlugin('cart');
        $view = $this->getView();

        $html =
            '<table class="std-table supply-table">'
                .'<thead>'
                    .'<tr>'
                        .'<th style="width: 35px"></th>'
                        .'<th style="width: 280px">Товар</th>'
                        .'<th style="width: 150px">Размер</th>'
                        .'<th style="width: 150px">Вкус</th>'
                        .'<th style="width: 100px" class="ta-c">На складе</th>'
                        .'<th style="width: 100px" class="ta-c">В Заказе</th>'
                        .'<th style="width: 100px" class="ta-c">Кол-во</th>'
                        .'<th style="width: 100px" class="ta-c">Стоимость за шт.</th>'
                        .'<th style="" class="ta-c"></th>'
                    .'</tr>'
                .'</thead>'
                .'<tbody>';
        
        foreach ($cart as $cartRow) {
            $size = $cartRow->getPlugin('size')->get('name');
            $product = $cartRow->getPlugin('product');
            
            $productUrl = $view->url('admin', ['module' => 'catalog', 'section' => 'products', 'action' => 'edit']) . '?id=' . $product->getId();

            $count = $cartRow->get('count');

            $taste = $cartRow->getPlugin('taste')->get('name');
            if(!in_array($taste, array('', 'Без вкуса'))) {
                $tasteStr = '<span style="color: red">' . $taste . '</span>';
            } else {
                $tasteStr = $taste;
            }

            $orderCount = ((int) $cartRow->get('order_count'));
            
            $html .=
                '<tr data-id="' . $cartRow->get('product_id') . '">'
                    .'<td class="check ta-c"><span><i class="fas fa-check"></i></span></td>'
                    .'<td class="product"><a target="_blank" href="' . $productUrl . '">' . $product->get('name') . '</a></td>'
                    .'<td>' . $size . '</td>'
                    .'<td>' . $tasteStr . '</td>'
                    .'<td class="ta-c pr-stock"><a href="' . $productUrl . '#edit-tabs=stock" target="_blank"><span>' . ((int) $cartRow->getPlugin('stock')->get('count')) . '</span> шт</a></td>'
                    .'<td class="ta-c">' . $orderCount . ' шт</td>'
                    .'<td>'
                        .'<div class="std-counter s">'
                            .'<div class="incr"></div>'
                            .'<input value="' . $count . '" min="0" max="' . $orderCount . '" class="cart-count" data-id="' . $cartRow->getId() . '">'
                            .'<div class="decr"></div>'
                        .'</div>'
                    .'</td>'
                    .'<td><input class="std-input cart-price" value="' . $cartRow->get('price') . '" data-id="' . $cartRow->getId() . '"></td>'
                    .'<td style="padding-left: 10px;"><a href="javascript:" class="cart-del" data-id="' . $cartRow->getId() . '">Удалить</a></td>'
                .'</tr>';
        }
        
        $html .=
                '</tbody>'
                .'<tfoot>'
                    .'<tr>'
                        .'<td colspan="6">'
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
                            .'Полная стоимость: <b> <span class="supply-price">' . $supply->get('price') . '</span></b>'
                            .'<br>'
                            .'<span class="btn sync-stocks">Синхронизировать склады</span>'
                        .'</td>'
                    .'</tr>'
                .'</tfoot>'
            .'</table>';

        return $html;
    }
}