<?php
namespace Catalog\View\Helper;

use Aptero\String\Numbers;
use Zend\Paginator\Paginator;
use Zend\View\Helper\AbstractHelper;

class ProductsList extends AbstractHelper
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
        $html =
            '<div class="products-list">';
        $script = '';

        $i = 0;
        foreach($products as $product) {
            $picClass = in_array($product->get('category_id'), [32, 33, 34]) ? ' pic-wire' : '';

            $i++;

            $url = '/goods/' . $product->get('url') . '/';

            $img = $product->getPlugin('image')->getImage('s');
            $reviews = $product->get('reviews');

            $html .=
                '<div class="product" data-id="' . $product->getId() . '" data-list="' . $options['list'] . '">'
                    .'<div class="pic' . $picClass . '">'
                        .'<a href="' . $url . '" class="pr-link img-box">'
                            .'<img src="' . $img . '" alt="' . $product->get('name') . '">'
                        .'</a>'
                        .'<div class="events">'
                            .($product->get('discount') ? '<div class="discount">-' . $product->get('discount') . '%</div>' : '')
                        .'</div>'
                        .'<div class="icons">'
                            .'<a href="' . $url . '" class="ico products-popup" rel="group">'
                                .'<i class="far fa-eye"></i>'
                                .'Быстрый просмотр'
                            .'</a>'
                        .'</div>'
                    .'</div>'
                    .'<div class="info">'
                        .'<div class="info-box">'
                            .'<a class="title" href="' . $url . '">' . $product->get('name') . '</a>'
                            .'<div class="props">';

            $strLn = 0;
            foreach($product->getPlugin('features') as $feature) {
                $strLn += (mb_strlen($feature->get('name')) / 25);
                if(!$strLn) {
                    $strLn = 1;
                } elseif($strLn > 4) {
                    break;
                }

                $html .=
                    '<div class="prop">' . $feature->get('name') . '</div>';
            }

            $html .=
                    '</div>'
                        .'</div>'
                        .'<div class="reviews-box">'
                            .$view->stars($product->get('stars'))
                            .'<a href="' . $url . 'reviews/#product-tabs" class="reviews pr-link">'
                                . ($reviews ? $reviews . ' ' . Numbers::declension($reviews, array('отзыв', 'отзыва', 'отзывов')) : '')
                            .'</a>'
                        .'</div>'

                        .'<div class="price-box">'
                            .'<div class="price"><span>' . $view->price($product->get('price')) . '</span> <i class="fas fa-ruble-sign"></i></div>'
                            .($product->get('discount') ? '<div class="price-old"><span>' . $view->price($product->get('price_old')) . '</span> <i class="fas fa-ruble-sign"></i></div>' : '')
                            .($product->get('stock') ? '<span href="/order/cart-form/?pid=' . $product->getId() . '" class="btn red to-cart popup">В корзину</span>' : '<span href="/order/cart-form/?pid=' . $product->getId() . '" class="btn to-request popup">Предзаказ</span>')
                        .'</div>'
                    .'</div>'
                .'</div>';

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