<?php
namespace Catalog\View\Helper;

use Aptero\String\Numbers;
use Zend\Paginator\Paginator;
use Zend\View\Helper\AbstractHelper;

class MobileProductsList extends AbstractHelper
{
    public function __invoke($products, $options = [])
    {
        if(!$products || !$products->count()) {
            return '<div class="empty-list">Товаров не найдено</div>';
        }

        $options = array_merge([
            'pagination'    => true,
            'list'          => 'Unknown',
        ], $options);

        $view = $this->getView();

        $script = '';
        $html =
           '<div class="products-list">';

        $i = 0;
        foreach($products as $product) {
            $i++;
            $url = '/goods/' . $product->get('url') . '/';

            $img = $product->getPlugin('image')->getImage('s');
            $reviews = $product->get('reviews');

            $html .=
                '<a href="' . $url . '" class="product pr-link" data-id="' . $product->getId() . '" data-list="' . $options['list'] . '">'
                    .'<div class="pic">'
                        .'<img src="' . $img . '" alt="' . $product->get('name') . '">'
                        .($product->get('discount') ? '<div class="discount">-' . $product->get('discount') . '%</div>' : '')
                    .'</div>'
                    .'<div class="info">'
                        .'<div class="name">' . $product->get('name') . '</div>'
                        .'<div class="pr-info">'
                            .$view->stars($product->get('stars'))
                            .'<div href="' . $url . 'reviews/#product-tabs" class="reviews">'
                                . ($reviews ? '(' . $reviews . ')' : '')
                            .'</div>'
                        .'</div>'
                    .'</div>'
                    .'<div class="order">'
                        .'<div class="price">от <span>' . $view->price($product->get('price')) . '</span> <i class="fas fa-ruble-sign"></i></div>'
                        .($product->get('discount') ? '<div class="price_old">от <span>' . $view->price($product->get('price_old')) . '</span> <i class="fas fa-ruble-sign"></i></div>' : '')
                        .($product->get('stock') ? '<div class="cart"><span href="/order/cart-form/?pid=1" class="btn red to-cart popup">В корзину</span></div>' : '<div class="stock not">нет в наличии</div>')
                        //.($product->get('stock') ? '<div class="stock">в наличии</div>' : '<div class="stock not">нет в наличии</div>')
                    .'</div>'
                .'</a>';

            $script .=
                'ga("ec:addImpression", {'
                    .'"id": "' . $product->getId() . '",'
                    .'"name": "' . $product->get('name') . '",'
                    .'"category": "' . $product->getPlugin('catalog')->get('name') . '",'
                    .'"brand": "' . $product->getPlugin('brand')->get('name') . '",'
                    .'"list": "List",'
                    .'"position": ' . $i
                .'});';
        }

        $html .=
                '<div class="clear"></div>'
            .'</div>';

        if($products instanceof Paginator && $options['pagination']) {
            $html .=
                $view->paginationControl($products, 'Sliding', 'pagination-slide', array('route' => 'application/pagination'));
        }

        $html .= '<script>' . $script . '</script>';

        return $html;
    }
}