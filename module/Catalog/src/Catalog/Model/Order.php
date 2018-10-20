<?php
namespace Catalog\Model;

use Application\Model\Region;
use Aptero\Db\Entity\Entity;
use Aptero\Db\Entity\EntityFactory;
use Delivery\Model\City;
use Delivery\Model\Delivery;
use Delivery\Model\Point;
use User\Model\Phone;

class Order extends Entity
{
    const STATUS_NEW        = 1;
    const STATUS_PENDING    = 3;
    const STATUS_PROCESSING = 5;
    const STATUS_COLLECTED  = 7;
    const STATUS_DELIVERY   = 10;
    const STATUS_COMPLETE   = 15;
    const STATUS_CANCELED   = 20;
    const STATUS_ABANDONED  = 25;
    const STATUS_PROBLEM    = 30;
    const STATUS_RETURN     = 35;

    const STATUS_PAID       = 1;
    const STATUS_UNPAID     = 0;

    static public $processStatuses = [
        self::STATUS_NEW        => 'Новый заказ',
        self::STATUS_PENDING    => 'В обработке',
        self::STATUS_PROCESSING => 'В обработке',
        self::STATUS_COLLECTED  => 'Собран',
        self::STATUS_DELIVERY   => 'В доставке',
        self::STATUS_COMPLETE   => 'Завершен',
        self::STATUS_CANCELED   => 'Отменен',
        self::STATUS_ABANDONED  => 'Не завершен',
        self::STATUS_PROBLEM    => 'Проблемы',
        self::STATUS_RETURN     => 'Возврат',
    ];

    static public $paidStatuses = [
        self::STATUS_PAID       => 'Оплачен',
        self::STATUS_UNPAID     => 'Не оплачен',
    ];

    public function __construct()
    {
        $this->setTable('orders');

        $this->addProperties([
            'user_id'     => [],
            'city_id'     => [],
            'phone_id'    => [],
            'adwords_id'  => [],
            'weight'      => [],
            'income'      => [],
            'outgo'       => [],
            'delivery_company' => [],
            'sl_id'            => [],
            'delivery_outgo'   => [],
            'delivery_income'  => [],
            'paid'        => [],
            'description' => [],
            'time_create' => [],
            'status'      => ['default' => self::STATUS_NEW],
            'full_price'  => ['virtual' => true],
            'order_id'    => ['virtual' => true],
            'address'     => ['virtual' => true],
        ]);

        $this->addPlugin('phone', function($model) {
            $phone = new Phone();
            $phone->setId($model->get('phone_id'));

            return $phone;
        }, array('independent' => true));

        $this->addPlugin('city', function($model) {
            $city = new City();
            $city->setId($model->get('city_id'));

            return $city;
        }, array('independent' => true));

        $this->addPlugin('cart', function($model) {
            $cart = Cart::getEntityCollection();
            $cart->select()->where(['order_id' => $model->getId()]);

            return $cart;
        });

        $this->addPlugin('attrs', function() {
            $props = new \Aptero\Db\Plugin\Attributes();
            $props->setTable('orders_attributes');

            return $props;
        });

        $this->addPropertyFilterOut('order_id', function($model) {
            return '347-' . $this->getId();
        });

        $this->addPropertyFilterOut('address', function($model) {
            $attrs = $model->getPlugin('attrs');

            if($attrs->get('delivery') == 'pickup') {
                $point = new Point();
                $point->setId($attrs->get('point'));
                $address = $point->get('address');
            } else {
                $address = $attrs->get('address');
            }

            return $address;
        });
    }

    public function getPublicId()
    {
        $dc = $this->get('delivery_company');
        $id = $this->getId();

        //if($dc == Delivery::COMPANY_INDEX_EXPRESS) {
            $id = '348-' . $id;
        //}

        return $id;
    }

    public function getPickupPoint()
    {
        $point = new Point();
        $point->setId($this->getPlugin('attrs')->get('point'));
        return $point;
    }

    public function getPrice()
    {
        return $this->get('income') + $this->get('delivery_income');
    }

    public function getProfit()
    {
        return ($this->get('income') + $this->get('delivery_income') - $this->get('delivery_outgo')) * 0.3;
    }

    public function isPaid()
    {
        return (($this->getPrice() * 0.98) - $this->get('paid')) < 30;
    }

    public function getDeliveryAddress()
    {
        $attrs = $this->getPlugin('attrs');

        $addressArr = [];

        if($attrs->get('index')) {
            $addressArr[] = $attrs->get('index');
        }

        if($attrs->get('city')) {
            $addressArr[] = 'г. ' . $attrs->get('city');
        }

        if($attrs->get('street')) {
            $addressArr[] = $attrs->get('street');
        }

        if($attrs->get('house')) {
            $addressArr[] = 'д. ' . $attrs->get('house');
        }

        if($attrs->get('building')) {
            $addressArr[] = 'корп. ' . $attrs->get('building');
        }

        if($flat = $attrs->get('flat')) {
            $addressArr[] = 'кв/оф ' . $attrs->get('flat');
        }

        return implode(', ', $addressArr);
    }

    public function getDeliveryInfo()
    {
        $info = [];
        $attrs = $this->getPlugin('attrs');

        $type = $attrs->get('delivery');

        $info['type']  = $type;
        $info['typeName'] = Delivery::getInstance()->deliveryTypes[$type];
        $info['id']    = $this->getId();
        $info['pid']   = $this->getPublicId();
        $info['price'] = $this->getPrice();

        if($type == 'courier') {
            $info['address'] = $this->getDeliveryAddress() . '. с ' . $attrs->get('time_from') . ' до ' . $attrs->get('time_to');
            $info['notice'] = 'Курьер позвонит вам за 40 минут до прибытия';
        } elseif($type == 'pickup') {
            $point = new Point();
            $point->setId($attrs->get('point'));
            $info['address'] = $point->get('address');
            $info['notice'] = 'Ожидайте SMS, подтверждающее поступление заказа на пункт самовоза '
                . $attrs->get('arriveDate') . ' после 15:00.';
        } elseif($type == 'post') {
            $point = new Point();
            $point->setId($attrs->get('point'));
            $info['address'] =  $this->getDeliveryAddress();
            $info['notice'] = 'Посылка будет отправлена в течении суток после оплаты заказа. Вы можете можете оплатить'
                .' заказ в любой момент в меню "что с моим заказом" в шапке сайта';
        }

        if($this->getPlugin('city')->isCapital()) {
            $info['payment'] = 'Вы можете оплатить заказ наличными при получении или онлайн: картой или Яндекс деньгами.';
        } else {
            $info['payment'] = 'Заказ будет отправлен после оплаты. Вы можете оплатить заказ сейчас или позже в меню "что с моим заказом" в шапке сайта.';
        }

        return $info;
    }
}