<?php
namespace Catalog\View\Helper;

use Zend\Paginator\Paginator;
use Zend\View\Helper\AbstractHelper;

class ProductItem extends AbstractHelper
{
    public function __invoke($product, $options = [])
    {
        $options = array_merge([
            'list'          => 'Unknown',
        ], $options);
        
        $url = '/goods/' . $product->get('url') . '/';
        $img = $product->getPlugin('image')->getImage('s');

        $view = $this->getView();

        $html =
            '<div class="wrap">'
                .'<div class="product" data-id="' . $product->getId() . '" data-list="' . $options['list'] . '">'
                    .'<a href="' . $url . '" class="pic pr-link">'
                        .'<img src="' . $img . '" alt="' . $product->get('name') . '">'
                        .($product->get('discount') ? '<div class="discount">-' . $product->get('discount') . '%</div>' : '')
                    .'</a>'
                    .'<a href="' . $url . '" class="name pr-link">' . $product->get('name') . '</a>'
                    . $view->stars($product->get('stars'))

                    .'<div class="price">'
                        . $view->price($product->get('price')) . ' <i class="fas fa-ruble-sign"></i>'
                    .'</div>'
                    .($product->get('discount') ? '<div class="price_old">от <span>' . $view->price($product->get('price_old')) . '</span> <i class="fas fa-ruble-sign"></i></div>' : '');

        if($product->get('stock')) {
            $html .=
                 '<span href="/order/cart-form/?pid=' . $product->getId() . '" class="btn s popup">В корзину</span>';
        } else {
            $html .=
                 '<span class="not-in-stock">нет в наличии</span>';
        }

        $html .=
            '<script>'
                .'ga("ec:addImpression", {'
                .'"id": "' . $product->getId() . '",'
                .'"name": "' . $product->get('name') . '",'
                .'"category": "' . $product->getPlugin('catalog')->get('name') . '",'
                .'"brand": "' . $product->getPlugin('brand')->get('name') . '",'
                .'"list": "Search and menu",'
                .'});'
            .'</script>';

        $html .=
                '</div>'
            .'</div>';

        return $html;
    }
}