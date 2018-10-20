<?php
namespace CatalogAdmin\View\Helper;

use Aptero\Db\Entity\EntityCollection;
use DeliveryAdmin\Model\Delivery;
use DeliveryAdmin\Model\Point;
use Zend\View\Helper\AbstractHelper;

class OrderDelivery extends AbstractHelper
{
    public function __invoke($order, $options = [])
    {
        if(!empty($options['index-express-full'])) {
            return $this->indexExpress($order, true);
        }

        $attrs = $order->getPlugin('attrs');

        switch ($attrs->get('delivery')) {
            case 'pickup':
                $point = new Point();
                $point->setId($attrs->get('point'));
				
                switch ($order->get('delivery_company')) {
                    case Delivery::COMPANY_INDEX_EXPRESS:
                        return $this->indexExpress($order);
                        break;
                    default:
                        return $this->indexExpress($order);
                }
                break;
            case 'courier':
                /*$delivery = new Delivery();
                $delivery->select()->where(['region_id' => $order->get('region_id')]);*/

                switch ($order->get('delivery_company')) {
                    case Delivery::COMPANY_INDEX_EXPRESS:
                        return $this->indexExpress($order);
                        break;
                    default:
                        return $this->indexExpress($order);
                }
                break;
            case 'post':
                return $this->russianPost($order);
                break;
        }

        return '';
    }

    protected function russianPost($order)
    {
        return 'Почта';
    }

    protected function shopLogistic($order)
    {
        $attrs = $order->getPlugin('attrs');
        $phone = $order->getPlugin('phone');

        $orderId = 'SP' . str_pad($order->getId(), 6, '0', STR_PAD_LEFT);

        //Таблица заказов
        $html =
            '<table class="std-table2 delivery-table">'
                .'<tr>'
                .'<th>Контактное лицо</th>'
                .'<th>Телефон</th>'
                .'<th>Стоимость</th>'
                .'<th>Город доставки</th>'
                .'<th>Дата передачи на склад</th>'
                .'<th>Пунтк выдачи</th>'
                .'<th>Телефон для смс</th>'
            .'</tr>';

        if($attrs->get('delivery') == 'courier') {
            $html .=
                '<tr>'
                    .'<td>' . $attrs->get('address') . '</td>'
                    .'<td>' . $attrs->get('time_from') . '</td>'
                    .'<td>' . $attrs->get('time_to') . '</td>'
                    .'<td>' . $orderId . '</td>'
                    .'<td></td>'
                    .'<td>' . $attrs->get('address') . '</td>'
                    .'<td>' . $attrs->get('name') . '</td>'
                    .'<td>' . str_replace('+7', '8', str_replace(array(' ', '-', '(', ')'), '', $phone->get('phone'))) . '</td>'
                    .'<td>' . ($order->get('price') + $order->get('delivery')) . '</td>'
                    .'<td>' . $order->get('price') . '</td>'
                    .'<td></td>'
                    .'<td>Позвонить за час перед доставкой</td>'
                    .'<td>' . $order->getCity()->get('name') . '</td>'
                    .'<td>' . $order->getCity()->get('name') . '</td>'
                    .'<td>' . ((new \DateTime())->modify('+1 day')->format('d/m/Y')) . '</td>'
                    .'<td>1</td>'
                    .'<td></td>'
                    .'<td></td>'
                . '</tr>';
        } else {

            $point = new \DeliveryAdmin\Model\Point();
            $point->setId($attrs->get('point'));

            $html .=
                '<tr>'
                    .'<td>' . $attrs->get('name') . '</td>'
                    .'<td>' . str_replace('+7', '8', str_replace(array(' ', '-', '(', ')'), '', $phone->get('phone'))) . '</td>'
                    .'<td>' . ($order->get('income') + $order->get('delivery_income')) . '</td>'
                    .'<td>' . $order->getCity()->get('name') . '</td>'
                    .'<td>' . ((new \DateTime())->modify('+1 day')->format('d/m/Y')) . '</td>'
                    .'<td>' . $point->get('address') . '</td>'
                    .'<td>' . str_replace('+7', '8', str_replace(array(' ', '-', '(', ')'), '', $phone->get('phone'))) . '</td>'
                . '</tr>';
        }

        $html .=
            '</table>';

        return $html;
    }

    protected function indexExpress($order, $full = false)
    {
        $view = $this->getView();

        $html =
            '<table class="std-table2">'
                .'<tr>'
                .'<th>№</th>'
                .'<th>Город</th>'
                .'<th>Адрес</th>'
                .'<th>Получатель</th>'
                .'<th>Телефон</th>'
                .'<th>Вес</th>'
                .'<th>Состав груза</th>'
                .'<th>Кол-во мест</th>'
                .'<th>Наложный платеж</th>'
                .'<th>Страховка</th>';


        //if($full || !$isPickup) {
            $html .=
                '<th>Примечания</th>'
                .'<th></th>'
                .'<th>Дата</th>'
                .'<th>С</th>'
                .'<th>По</th>';
        //}
        $html .=
            '</tr>';

        if($order instanceof EntityCollection) {
            $orders = $order;
        } else {
            $orders = [$order];
        }

        foreach ($orders as $order) {
            $cart = $order->getPlugin('cart');
            $attrs = $order->getPlugin('attrs');
            $phone = $order->getPlugin('phone');

            $isPickup = $attrs->get('delivery') == 'pickup';

            if ($isPickup) {
                $point = new \DeliveryAdmin\Model\Point();
                $point->setId($attrs->get('point'));
            }

            if ($isPickup) {
                $address = 'Самовывоз - ' . $point->get('address') . ($point->get('metro') ? ' (метро ' . $point->get('metro') . ')' : '');
            } else {
                $address = 'Доставка - ' . $order->getDeliveryAddress();
            }

            $html .=
                '<tr>'
                . '<td>' . $order->getId() . '</td>'
                . '<td>' . $order->getCity()->get('name') . '</td>'
                . '<td>' . $address . '</td>'
                . '<td>' . $attrs->get('name') . '</td>'
                . '<td>8' . substr(str_replace('+7', '8', str_replace(array(' ', '-', '(', ')'), '', $phone->get('phone'))), 1) . '</td>'
                . '<td>' . $cart->count() . '</td>';

            $tmp = '';
            foreach ($cart as $row) {
                $tmp .= $row->getPlugin('product')->get('name') . ', ';
            }
            $tmp = rtrim($tmp, ', ');

            $html .=
                '<td>' . $tmp . '</td>'
                . '<td>1</td>';

            if ($order->isPaid()) {
                $html .=
                    '<td>0</td>'
                    . '<td>' . $order->get('income') . '</span></td>';
            } else {
                $html .=
                    '<td><span class="order-full-price">' . $order->getPrice('income') . '</span></td>'
                    . '<td>' . $order->get('income') . '</td>';
            }

            if (!$isPickup) {
                $html .=
                    '<td>Дата: ' . $attrs->get('date') . ' с ' . $attrs->get('time_from') . ' по ' . $attrs->get('time_to') . '</td>'
                    . '<td></td>'
                    . '<td>' . $attrs->get('date') . '</td>'
                    . '<td>' . $attrs->get('time_from') . '</td>'
                    . '<td>' . $attrs->get('time_to') . '</td>';
            } else {
                $html .=
                    '<td></td>'
                    . '<td></td>'
                    . '<td></td>'
                    . '<td></td>'
                    . '<td></td>';
            }

            $html .=
                '</tr>';
        }

        $html .=
            '</table>';

        $html .=
            '<table class="std-table2 delivery-table">'
            . '<tr>'
            . '<th>№ заказа</th>'
            . '<th>Артикул</th>'
            . '<th>Наименование</th>'
            . '<th>Кол-во</th>'
            . '<th>Цена</th>'
            . '<th>Сумма</th>'
            . '<tr>';

        foreach ($orders as $order) {
            foreach ($cart = $order->getPlugin('cart') as $row) {
                if(!$row->get('count')) {
                    continue;
                }

                $product = $row->getPlugin('product');
                $html .=
                    '<tr>'
                    . '<td>347-' . $order->getId() . '</td>'
                    . '<td>P' . str_pad($product->getId(), 4, '0', STR_PAD_LEFT) . '</td>'
                    . '<td>' . $product->get('name') . '</td>'
                    . '<td>' . $row->get('count') . '</td>'
                    . '<td>' . $row->get('price') . '</td>'
                    . '<td>' . ($row->get('price') * $row->get('count')) . '</td>'
                    . '</tr>';
            }
        }

        $html .=
            '</table>';

        return $html;
    }
}