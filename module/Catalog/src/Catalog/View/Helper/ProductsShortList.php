<?php
namespace Catalog\View\Helper;

use Zend\Paginator\Paginator;
use Zend\View\Helper\AbstractHelper;

class ProductsShortList extends AbstractHelper
{
    public function __invoke($products)
    {
        if(!$products->count()) {
            return '<div class="empty-list">Товаров не найдено</div>';
        }

        $view = $this->getView();

        $html =
            '<div class="products-short-list">';

        foreach($products as $product) {
            $html .= $view->productItem($product);
        }

        $html .=
            '</div>';

        return $html;
    }
}