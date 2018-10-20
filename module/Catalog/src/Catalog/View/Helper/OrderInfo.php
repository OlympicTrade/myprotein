<?php
namespace Catalog\View\Helper;

use Catalog\Model\Order;
use Zend\View\Helper\AbstractHelper;

class OrderInfo extends AbstractHelper
{
    public function __invoke(Order $order)
    {
        $html = '';

        if(in_array($order->get('status'), [Order::STATUS_CANCELED, Order::STATUS_RETURN, Order::STATUS_ABANDONED])) {
            return '<div class="header">Заказ <b>' . $order->getPublicId() . '</b> отменен</div>';
        }

        if($order->get('status') == Order::STATUS_COMPLETE) {
            return '<div class="header">Заказ <b>' . $order->getPublicId() . '</b> выполнен</div>';
        }

        $html .= '<div class="header">Заказ <b>' . $order->getPublicId() . '</b></div>';


        $attrs = $order->getPlugin('attrs');

        $view = $this->getView();

        $isPickup = $attrs->get('delivery') == 'pickup';

        if($attrs->get('delivery') == 'pickup') {
            $point = new \DeliveryAdmin\Model\Point();
            $point->setId($attrs->get('point'));

            $html .=
                '<div class="sum">'
                    .'<div class="pickup">'
                        .'<div><b>Самовывоз ' . $attrs->get('arriveDate') . '</b></div>'
                        .'<div>' . $point->get('address') . '</div>'
                        .'<a href="/delivery/points/?type=view&pid=' . $point->getId() . '" class="popup">показать на карте</a>'
                    .'</div>'
                    .'<div class="price">'
                        .'Сумма ' . $view->price($order->getPrice()) . ' <i class="fas fa-ruble-sign"></i>'
                        . ($order->isPaid() ? ' (оплачено)' : '')
                    .'</div>'
                .'</div>';
        } elseif($attrs->get('delivery') == 'courier') {
            $html .=
                '<div class="sum">'
                    .'<div class="delivery">'
                        .'<div>Доставка ' . $attrs->get('date') . ' c ' . $attrs->get('time_from') . ' до ' . $attrs->get('time_to') . '</div>'
                    .'</div>'
                    .'<div class="price">'
                        .'Сумма ' . $view->price($order->getPrice()) . ' <i class="fas fa-ruble-sign"></i>'
                        . ($order->isPaid() ? ' (оплачено)' : '')
                    .'</div>'
                .'</div>';
        } elseif($attrs->get('delivery') == 'post') {
            $html .=
                '<div class="sum">'
                    .'<div class="delivery">'
                        .'<div>Почта России</div>'
                        .'</div>'
                        .'<div class="price">'
                        .'Сумма ' . $view->price($order->getPrice()) . ' <i class="fas fa-ruble-sign"></i>'
                            . ($order->isPaid() ? ' (оплачено)' : '')
                    .'</div>'
                .'</div>';
        } else {
            $html .=
                '<div class="sum">'
                .'<div class="delivery">'
                    .'<div>Не выбран тип доставки</div>'
                .'</div>'
                .'<div class="price">'
                    .'Сумма ' . $view->price($order->getPrice()) . ' <i class="fas fa-ruble-sign"></i>'
                    . ($order->isPaid() ? ' (оплачено)' : '')
                .'</div>'
                .'</div>';
        }

        $statusStr = '';

        switch($order->get('status')) {
            case Order::STATUS_PROCESSING:
                $statusStr .= 'Комплектуеться на складе';
                break;
            case Order::STATUS_PENDING:
                $statusStr .= 'Комплектуеться на складе';
                break;
            case Order::STATUS_DELIVERY:
                $statusStr .= 'Собран и отправлен в доставку';
                if($isPickup) { $statusStr .= '<br>Ожидайте SMS о прибыти на точку выдачи'; }
                break;
            case Order::STATUS_NEW:
                $statusStr .= 'Заказ проверяеться менеджером';
                break;
            case Order::STATUS_PROBLEM:
                $statusStr .= 'Заказ проверяеться менеджером';
                break;
            default:
                throw new \Exception('Unknown order status');
                break;
        }

        $html .=
            '<div class="status">'
                .'<div class="title">Статус заказа</div>'
                . $statusStr;

        if(!$order->isPaid()) {
            $html .= '<a class="btn orange pay-btn" href="/payment/pay/?o=' . $order->getId() . '">Оплатить Online</a>';
        }else {
            $html .= '<div>Заказ оплачен</div>';
        }

        $html .=
            '</div>';




        $html .=
            '<span class="btn" data-fancybox-close>Закрыть</span>';

        return $html;
    }
}