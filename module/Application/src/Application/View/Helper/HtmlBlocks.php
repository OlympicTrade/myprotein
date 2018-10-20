<?php
namespace Application\View\Helper;

use Application\Model\Menu;
use Application\Model\MenuItems;
use Zend\View\Helper\AbstractHelper;

class HtmlBlocks extends AbstractHelper
{
    public function __invoke($block)
    {
        switch($block) {
            case 'delivery':
                return $this->delivery();
                break;
            default:
        }

        return '';
    }

    public function delivery()
    {
        $html =
            '<div class="help-block">'
                .'<div class="col order">'
                    .'<a href="/delivery/">Как сделать заказ?</a>'
                .'</div>'
                .'<div class="col payment">'
                    .'<a href="/delivery/">Как оплатить? </a>'
                .'</div>'
                .'<div class="col return">'
                    .'<a href="/delivery/">Возврат товара</a>'
                .'</div>'
                .'<div class="col delivery">'
                    .'<a href="/delivery/">Доставка</a>'
                .'</div>'
                .'<div class="clear"></div>'
            .'</div>';

        return $html;
    }
}