<?php
namespace Catalog\View\Helper;

use Catalog\Model\Order;
use Zend\View\Helper\AbstractHelper;

class OrdersList extends AbstractHelper
{
    public function __invoke($orders)
    {
        if(!$orders->count()) {
            return '<div class="empty-list">У вас пока нет заказов</div>';
        }

        $html =
            '<div class="orders-list">';

        $view = $this->getView();
        foreach($orders as $order) {
            $html .=
                '<div class="order" data-status="' . $order->get('status') . '" data-id="' . $order->getId() . '">'
                    .'<div class="header">'
                        .'<div class="order-name">Заказ №' . $order->getId() . '</div>'
                        .'<div class="date">' . $view->date($order->get('time_create')) . '</div>'
                        .'<div class="price">Сумма: <b>' . $view->price($order->getPrice()) . '</b> <i class="fas fa-ruble-sign"></i></div>'
                        .'<div class="col status">Статус: <b>' . Order::$processStatuses[$order->get('status')] . '</b></div>'
                    .'</div>'
                    .'<div class="body"></div>'
                .'</div>';
        }

        $html .=
                '<div class="clear"></div>'
            .'</div>';


        return $html;
    }
}