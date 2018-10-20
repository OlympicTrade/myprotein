<?php
namespace Catalog\View\Helper;

use Aptero\String\Numbers;
use Zend\Paginator\Paginator;
use Zend\View\Helper\AbstractHelper;

class BrandsList extends AbstractHelper
{
    public function __invoke($brands)
    {
        $view = $this->getView();

        $html =
           '<div class="brands-list">';

        foreach($brands as $brand) {
            $url = '/brands/' . $brand->get('url') . '/';

            $html .=
                '<a href="' . $url . '" class="brand">'
                    .'<div class="title">' . $brand->get('name') . '</div>'
                    .'<div class="count">' . $brand->get('count') . ' ' . Numbers::declension($brand->get('count'), array('товар', 'товара', 'товаров')) . '</div>'
                .'</a>';
        }

        $html .=
                '<div class="clear"></div>'
            .'</div>';

        return $html;
    }
}