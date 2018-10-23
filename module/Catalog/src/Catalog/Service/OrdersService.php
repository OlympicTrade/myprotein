<?php

namespace Catalog\Service;

use Aptero\Db\Entity\EntityFactory;
use Aptero\Service\AbstractService;
use CallcenterAdmin\Model\Call;
use Catalog\Model\Order;
use Catalog\Model\ProductRequest;
use Delivery\Model\City;
use Delivery\Model\Delivery;
use Delivery\Model\Point;
use Metrics\Model\Adwords;
use User\Service\AuthService;
use Zend\Db\Sql\Expression;

class OrdersService extends AbstractService
{
    public function resendConfirmSms($orderId)
    {
        $order = new Order();
        $order->setId($orderId);
        $phone = $order->getPlugin('phone');

        $this->getUserService()->confirmSms($phone, $this->getOrderConfirmCode($order) . ' - Код подтверждения заказа');

        return true;
    }

    public function orderStep3($oid, $code)
    {
        $orderId = (int) $oid;

        if(!$orderId) {
            return false;
        }

        $order = new Order();
        $order->setId($orderId);

        if(!$order->load()) {
            return false;
        }

        $phone = $order->getPlugin('phone');

        if($phone->get('confirmed')) {
            return true;
        }

        if($this->getOrderConfirmCode($order) != $code) {
            return false;
        }

        $phone->set('confirmed', '1')->save();
        $this->orderMsg($order);
        $this->checkOrder($order);

        return true;
    }

    public function orderStep2($data)
    {
        $orderId = (int) $data['oid'];
        unset($data['oid']);

        if(!$orderId) {
            return false;
        }

        $order = new Order();
        $order->setId($orderId);
        $order->select()->where(['status' => Order::STATUS_NEW]);

        if(!$order->load()) {
            return false;
        }

        $phone = $order->getPlugin('phone');
        if(!$phone->get('confirmed')) {
            $this->getUserService()->confirmSms($phone, $this->getOrderConfirmCode($order) . ' - Код подтверждения заказа');
        }

        if($data['attrs-delivery'] == 'pickup') {
            $data['attrs-date'] = $this->getDeliveryService()->getDeliveryDates([
                'price'    => $order->get('income'),
                'weight'   => $order->get('weight'),
            ], $data['attrs-delivery'])->format('d.m.Y');
        }

        $order->unserializeArray($data);
        $this->updateOrderPrice($order);
        $order->save();

        if($phone->get('confirmed')) {
            $this->orderMsg($order);
            $this->checkOrder($order);
        }

        return $order;
    }

    public function getOrderConfirmCode($order)
    {
        return substr(crc32($order->getId() . $order->getPlugin('phone')->get('phone') . '2j34df'), 1, 4);
    }

    public function orderStep1($data)
    {
        //Закрытие повторяющихся заказов
        $phone = $this->getUserService()->addPhone($data['phone']);

        $orderId = (int) $data['oid'];
        unset($data['oid']);

        $prevOrder = new Order();
        $prevOrder->select()
            ->columns(['id'])
            ->where
            ->greaterThan('time_create', (new \DateTime())->modify('-20 minutes')->format('Y-m-d H:i:s'))
            ->equalTo('status', Order::STATUS_NEW)
            ->equalTo('phone_id', $phone->getId());

        if($orderId) {
            $prevOrder->select()->where->notEqualTo('id', $orderId);
        }

        if($prevOrder->load()) {
            $prevOrder->set('status', Order::STATUS_CANCELED)->save();
            $this->cleanOrder($prevOrder->getId());
        }

        //Создание нового заказа
        if($orderId) {
            $order = new Order();
            $order->setId($orderId);
            $order->select()->where(['status' => Order::STATUS_NEW]);
        }

        if(!$orderId || !$order->load()) {
            $order = $this->createEmptyOrder($data);
        }

        $order->unserializeArray($data);

        $order->set('phone_id', $phone->getId());
        $order->save();

        return $order;
    }

    /**
     * @return \Aptero\Db\Entity\EntityCollection
     */
    public function getOrders()
    {
        $user = AuthService::getUser();

        $orders = EntityFactory::collection(new Order());

        $orders->select()
            ->order('id DESC')
            ->where(array(
                'user_id'   => $user->getId()
            ))->where
            ->notIn('status', [Order::STATUS_ABANDONED, Order::STATUS_CANCELED]);

        return $orders;
    }

    public function newProductRequest($product, $data)
    {
        $request = new ProductRequest();
        $request->setVariables([
            'product_id'  => $product->getId(),
            'price_id'    => $product->get('size_id'),
            'taste_id'    => $product->get('taste_id'),
            'contact'     => $data['contact'],
        ])->save();

        return $request;
    }

    public function orderMsg($order)
    {
        $messages = [];

        $attrs = $order->getPlugin('attrs');
        $sms = $this->getServiceManager()->get('Sms');

        if($attrs->get('delivery') == 'pickup') {
            $point = new Point();
            $point->setId($attrs->get('point'));
            $deliveryDate = $point->getDeliveryDate();

            $sms->send(
                $order->getPlugin('phone')->get('phone'),
                'Заказ ' . $order->getPublicId() . ' (' . $order->getPrice() . ' руб) Самовывоз ' . $point->get('address')
                .'. Ожидайте SMS о поступлении заказа на пункт выдачи ' . $deliveryDate->format('d.m') . ' полсе 16:00'
            );
        } elseif($attrs->get('delivery') == 'courier') {
            $messages[] = 'Заказ будет доставлен ' . $attrs->get('date') . ' с ' . $attrs->get('time_from') . ' по ' . $attrs->get('time_to') . ' по адресу ' . $attrs->get('address');

            $sms->send(
                $order->getPlugin('phone')->get('phone'),
                'Заказ ' . $order->getPublicId() . ' (' . $order->getPrice() . ' руб) Доставка ' . $attrs->get('date') . ' с ' . $attrs->get('time_from') . ' до ' . $attrs->get('time_to')
            );
        } else {
            $messages[] = 'Заказ будет отправлен на следующий день после получения оплаты. При поступлении денег и отправке заказа вам будет выслано SMS подтверждение и трекер Почты России.';

            /*$sms->send(
                $order->getPlugin('phone')->get('phone'),
                'Заказ ' . $order->getPublicId() . ' (' . $order->getPrice() . ' руб). Требуеться оплата для отправки по адресу: ' . $attrs->get('index') . ' ' . $attrs->get('address')
            );*/
        }

        $messages[] = 'Номер заказа <b>' . $order->getPublicId() . '</b>. К оплате <b>' . preg_replace('/(\d)(?=(\d\d\d)+([^\d]|$))/i', '$1 ', ($order->getPrice())) . '</b> руб.';

        $paymentUrl = '/payment/pay/?id=' . $order->getId() . '&p=' . crc32($order->getPlugin('phone')->get('phone'));

        if($attrs->get('delivery') == 'post') {
            $messages[] = '<a target="_blank" href="' . $paymentUrl . '" class="btn payment-btn">Оплатить Online</a>';
        } else {
            $messages[] = 'Вы можете оплатить заказ при получении или <a target="_blank" href="' . $paymentUrl . '" class="btn payment-btn">Оплатить Online</a>';
        }
        return $messages;
    }

    public function updateOrderPrice(Order $order)
    {
        if(!$order->load()) {
            return false;
        }

        $price  = 0;
        $weight = 0;
        if($order->get('status') == Order::STATUS_NEW) {
            foreach ($order->getPlugin('cart') as $cart) {
                $price += $cart->get('price') * $cart->get('order_count');
                $weight += $cart->get('weight');
            }
        }else {
            foreach ($order->getPlugin('cart') as $cart) {
                $price += $cart->get('price') * $cart->get('count');
                $weight += $cart->get('weight');
            }
        }

        $attrs = $order->getPlugin('attrs');
        $deliveryInfo = $this->getDeliveryService()->getDeliveryPrice([
            'price'  => $price,
            'cityId' => $order->get('city_id'),
        ], $attrs->get('delivery'));

        //if(!$order->get('delivery_company')) {
            $order->set('delivery_company', $order->getPlugin('city')->detectDeliveryCompany($order));
        //}

        $order->setVariables([
            'weight'   => $weight,
            'income'   => $price,
            'outgo'    => $this->calcOrderOutgo($order),
            'delivery_income' => $deliveryInfo['income'],
            'delivery_outgo'  => $deliveryInfo['outgo'],
        ]);

        return $order;
    }

    public function calcOrderOutgo(Order $order)
    {
        $dateFrom = new \DateTime();

        $bSelect = $this->getSql()->select();
        $bSelect->from(['sp' => 'supplies_products'])
            ->columns(['b_price' => new Expression('AVG(sp.price * s.currency_rate)')])
            ->join(['s' => 'supplies'], 's.id = sp.supply_id', [])
            ->where([
                'sp.product_id' => new Expression('c.product_id'),
                'sp.taste_id'   => new Expression('c.taste_id'),
                'sp.size_id'    => new Expression('c.size_id'),
            ])
            ->where
            ->greaterThan('sp.price', 0)
            ->greaterThan('s.date', $dateFrom->modify('-3 month')->format('Y-m-d'));

        $select = $this->getSql()->select();
        $select
            ->from(['o' => 'orders'])
            ->columns(['b_price' => $bSelect])
            ->join(['c' => 'orders_cart'], 'c.order_id = o.id', ['count' => new Expression('SUM(c.count)'), 'c_price' => 'price'])
            ->group('c.product_id')->group('c.size_id')->group('c.taste_id')
            ->where(['o.id' => $order->getId()]);

        $outgo = 0;
        foreach ($this->execute($select) as $row) {
            if($row['b_price']) {
                $outgo += $row['b_price'] * $row['count'];
            } else {
                $outgo += ($row['c_price'] * 0.6) * $row['count'];
            }
        }

        return (int) $outgo;
    }

    public function cleanOrder($orderId)
    {
        $order = new Order();
        $order->setId($orderId);

        if(!$order->load()) {
            return false;
        }

        foreach ($order->getPlugin('cart') as $cartRow) {
            $this->changeProductCount($cartRow->get('product_id'), $cartRow->get('taste_id'), $cartRow->get('size_id'), $cartRow->get('count'));
            $cartRow->set('count', 0)->save();
        }

        $order->setVariables([
            'delivery_income' => 0,
            'delivery_outgo'  => 0,
            'income'   => 0,
            'outgo'    => 0,
        ])->save();

        return true;
    }

    public function changeProductCount($productId, $tasteId, $sizeId, $count) {
        $sql = $this->getSql();

        $where = [
            'product_id' => $productId,
            'size_id'    => $sizeId,
            'taste_id'   => $tasteId,
        ];

        $select = $sql->select('products_stock');
        $select->where($where);
        $result = $this->execute($select);

        $diff = 0;

        if(count($result)) {
            if(($result->current()['count'] + $count) < 0) {
                $count = -$result->current()['count'];

                $delete = $sql->delete('products_stock');
                $delete->where($where);

                $this->execute($delete);
            } else {
                $diff = $result->current()['count'] + $count;

                $update = $sql->update('products_stock');
                $update->where($where)->set(['count' => $diff]);

                $this->execute($update);
            }
        } elseif($count > 0) {
            $insert = $sql->insert('products_stock');
            $insert->values($where + ['count' => $count]);

            $this->execute($insert);

            $diff = $count;
        } else {
            $diff = 0;
            $count = 0;
        }

        return [
            'count' => abs($count), //Сколько реально добавлено/вычтено товара
            'stock' => $diff,       //Остаток
        ];
    }

    /**
     * @return Order
     */
    protected function createEmptyOrder()
    {
        $order = new Order();

        $authService = new AuthService();
        if($user = $authService->getIdentity()) {
            $order->set('user_id', $user->getId());
        }

        $order->setVariables([
            'status'    => Order::STATUS_NEW,
            'city_id' => Delivery::getInstance()->getCity()->getId()
        ]);

        $this->updateMetricsInfo($order);

        $order->save();

        $cart = $this->getCartService()->getCookieCart();

        foreach($cart as $row) {
            $row->set('order_id', $order->getId());

            $row->setVariables([
                'order_id'    => $order->getId(),
                'count'       => 0,
                'order_count' => $row->get('count'),
            ]);
        }

        $cart->save();
        $this->updateOrderPrice($order);

        return $order;
    }

    public function updateMetricsInfo($order)
    {
        if($adwords = (new Adwords)->loadFromCookie()) {
            $order->set('adwords_id', $adwords->getId());
        }

        $platform = new \Aptero\Seo\Platform();
        $order->getPlugin('attrs')->set('platform', $platform->getPlatform());
    }

    public function checkOrders($orderIds = null)
    {
        $orders = Order::getEntityCollection();

        $dt = new \DateTime();
        $dt->modify('-25 min');

        $orders->select()
            ->where
            ->equalTo('status', Order::STATUS_NEW)
            ->lessThanOrEqualTo('time_create', $dt->format('Y-m-d H:i:s'));

        foreach ($orders as $order) {
            $this->checkOrder($order);
            sleep(0.15);
        }
    }

    public function checkOrder($order)
    {
        $attrs = $order->getPlugin('attrs');
        $phone = $order->getPlugin('phone');

        $troubles = [];

        if(!$attrs->get('delivery')) {
            $troubles[] = 'Не выбран тип доставки';
        }

        if(!$phone->get('confirmed')) {
            $troubles[] = 'Телефон не проверен';
        }

        $stockTroubles = [];
        foreach($order->getPlugin('cart') as $row) {
            $stock = $this->changeProductCount(
                $row->get('product_id'),
                $row->get('taste_id'),
                $row->get('size_id'),
                -$row->get('order_count')
            );

            $row->set('count', $stock['count'])->save();

            if($row->get('count') != $row->get('order_count')) {
                $product = $row->getPlugin('product');
                $stockTroubles[] = ' - ' . $product->get('name') . ' (в заказе ' . $row->get('order_count') . ', у нас ' . $row->get('count') . ')';
            }
        }

        if($stockTroubles) {
            $troubles[] = 'Нет товаров:' . "\n" . implode("\n\t", $stockTroubles);
        }

        if($troubles) {
            $order->set('status', Order::STATUS_PROBLEM);

            $this->getCallcenterService()->addCall([
                'type_id'    => Call::TYPE_ORDER,
                'item_id'    => $order->getId(),
                'phone_id'   => $phone->getId(),
                'name'       => $attrs->get('name'),
                'theme'      => 'Проблемы с заказом',
                'desc'       => implode("\n", $troubles),
            ]);
        } else {
            $order->set('status', Order::STATUS_PROCESSING);
        }

        $sms = $this->getServiceManager()->get('Sms');
        $sms->send('79523885885', '+' . $phone->get('phone') . "\n" . 'https://olympic-trade.ru/admin/catalog/orders/edit/?id=' . $order->getId());
		
        $order->save();
    }

    public function checkCode($orderId, $code)
    {
        $order = new Order();
        $order->setId($orderId);

        if(!$order->load()) {
            return false;
        }

        $phone = $order->getPlugin('phone');

        if($phone->get('sms_code') != $code) {
            return false;
        }

        $phone->set('confirmed', '1')->save();

        return true;
    }

    /**
     * @return \Catalog\Service\CartService
     */
    protected function getCartService()
    {
        return $this->getServiceManager()->get('Catalog\Service\CartService');
    }

    /**
     * @return \User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->getServiceManager()->get('User\Service\UserService');
    }

    /**
     * @return \Delivery\Service\DeliveryService
     */
    protected function getDeliveryService()
    {
        return $this->getServiceManager()->get('Delivery\Service\DeliveryService');
    }

    /**
     * @return \CallcenterAdmin\Service\CallcenterService
     */
    protected function getCallcenterService()
    {
        return $this->getServiceManager()->get('CallcenterAdmin\Service\CallcenterService');
    }

    /**
     * @return \Sync\Service\SyncService
     */
    protected function getSyncService()
    {
        return $this->getServiceManager()->get('Sync\Service\SyncService');
    }
}