<?php
namespace Catalog\View\Helper;

use Zend\Form\Element\Select;
use Zend\View\Helper\AbstractHelper;

class OrderCartList extends AbstractHelper
{
    public function __invoke($order)
    {
        $view = $this->getView();
		
		$html =
            '<div class="col-l">'
                .'<div class="cart-list">';

        foreach($order->getPlugin('cart') as $cartRow) {
            $product = $cartRow->getPlugin('product');
            $url = '/goods/' . $product->get('url') . '/';

            $html .=
                '<div class="product" data-product_id="' . $product->getId() . '" data-price_id="' . $cartRow->get('price_id') . '" data-taste_id="' . $cartRow->get('taste_id') . '">'
                    .'<a href="' . $url . '" class="pic">'
                        .'<img src="' . $product->getPlugin('image')->getImage('s') . '" alt="' . $product->get('name') . '">'
                    .'</a>'
                    .'<div class="info">'
                        .'<a href="' . $url . '" class="name">' . $product->get('name') . '</a>'
                        .'<div class="props">'
                            .'<div class="row">'
                                .'<div class="label">Вкус:</div>'
                                . $product->get('taste')
                            .'</div>'
                            .'<div class="row">'
                                .'<div class="label">Размер:</div>'
                                . $product->get('size')
                            .'</div>'
                        .'</div>'
                        .'<div class="price-box">'
                            .'<div class="sum"><span>' . $view->price($product->get('price') * $cartRow->get('count')) . '</span> <i class="fas fa-ruble-sign"></i></div>'
                            .'<div class="per-unit"><b>' . $view->price($product->get('price')) . '</b> <i class="fas fa-ruble-sign"></i> X <b>' . $cartRow->get('count') . '</b></div>'
                        .'</div>'
                    .'</div>'
                .'</div>';
        }

        $html .=
                    '</div>'
                .'</div>'
                .'<div class="col-r">'
                    .''
                .'</div>'
                .'<div class="clear"></div>'
            .'</div>';

        /*
        $html =
            '<table class="cart-table">';

        $attrs = $order->getPlugin('attrs');

        foreach($order->getPlugin('cart') as $cartRow) {
            $product = $cartRow->getPlugin('product');
            $url = '/goods/' . $product->get('url') . '/';

            $options = array(1 => 1);
            for($i = 2; $i < $product->get('count') && $i <= 15; $i++) {
                $options[$i] = $i;
            }
            $select = new Select('count', array(
                'options'   => $options,
            ));
            $select->setAttributes(array(
                'class'     => 'std-select js-cart-count',
                'data-id'   => $product->getId(),
            ));
            $select->setValue($cartRow->get('count'));

            $html .=
                '<tr class="product" data-id="' . $product->getId() . '">'
                    .'<td class="pic">'
                        .'<a href="' . $url . '"><img src="' . $product->getPlugin('image')->getImage('s') . '" alt=""></a>'
                    .'</td>'
                    .'<td class="name">'
                        .'<a href="' . $url . '">' . $product->get('name') . '</a>'
                    .'</td>'
                    .'<td class="count">'
                        . $view->formSelect($select)
                        .'шт – ' . $view->price($product->get('price')) . ' <i class="fas fa-ruble-sign"></i>'
                    .'</td>'
                    .'<td class="bonus">'
                        . '<b>' . $view->price($product->get('price') * $cartRow->get('count')) . '</b>' . ' <i class="fas fa-ruble-sign"></i>'
                    .'</td>'
                    .'<td class="close"></td>'
                .'</tr>';
        }

        if($attrs->get('delivery') == 'delivery') {
            $delivery =
                '<div class="title">Доставка:</div>'
                .$attrs->get('address');
        } else {
            $delivery =
                '<div class="title">Доставка:</div>'
                .'Самовывоз';
        }

        if($attrs->get('payment') == 'bill') {
            $payment =
                '<div class="title">Олата:</div>'
                .'По счету';
        } else {
            $payment =
                '<div class="title">Олата:</div>'
                .'Наличными';
        }

        $html .=
            '<tr>'
                .'<td colspan="2" class="delivery">' . $delivery . '</td>'
                .'<td class="payment">' . $payment . '</td>'
                .'<td colspan="2" class="summary">'
                    .'<div class="title">Итого:</div>'
                    .'8 260 <i class="fas fa-ruble-sign"></i>'
                .'</td>'
            .'</tr>';

        $html .=
            '</table>';*/

        return $html;
    }
}