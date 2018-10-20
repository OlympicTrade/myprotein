<?php
namespace Catalog\View\Helper;

use Zend\Paginator\Paginator;
use Zend\View\Helper\AbstractHelper;

class MobileProductsShortList extends AbstractHelper
{
    public function __invoke($products, $options = [])
    {
        if(!$products->count()) {
            return '<div class="empty-list">Товаров не найдено</div>';
        }

        $options = array_merge([
            'list'          => 'Unknown',
        ], $options);

        $view = $this->getView();

        $html =
            '<div class="products-short-list">';

        foreach($products as $product) {
            $img = $product->getPlugin('image')->getImage('s');
            $url = '/goods/' . $product->get('url') . '/';

            $html .=
                '<div class="product pr-link" data-id="' . $product->getId() . '" data-list="' . $options['list'] . '">'
                    .'<a href="' . $url . '" class="pic">'
                        .'<img src="' . $img . '" alt="' . $product->get('name') . '">'
                    .'</a>'
                    .'<a href="' . $url . '" class="name">' . $product->get('name') . '</a>'
                    .'<div class="info">'
                        .$view->stars($product->get('stars'))
                        .'<div class="in-stock ' . ($product->get('stock') ? '' : 'not') . '">' . ($product->get('stock') ? 'В наличии' : 'отсутсвует') . '</div>'
                    .'</div>'
                    .'<div class="price"><span class="from">от</span> ' . $view->price($product->get('price')) . ' <i class="fas fa-ruble-sign"></i></div>'
                .'</div>';
        }

        $html .=
            '</div>';

        return $html;
    }
}