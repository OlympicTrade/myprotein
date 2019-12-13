<?php
namespace CatalogAdmin\Model;

use Aptero\Db\Entity\Entity;
use Aptero\Db\Entity\EntityFactory;
use CallcenterAdmin\Model\Call;
use Delivery\Model\City;
use DeliveryAdmin\Model\Point;
use ManagerAdmin\Model\Task;
use Metrics\Model\Adwords;
use UserAdmin\Model\Phone;
use UserAdmin\Model\User;

/**
 * @method static Orders getEntityCollection()
 */
class Orders extends Entity
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
    const STATUS_WAITING    = 40;

    const STATUS_PAID       = 1;
    const STATUS_UNPAID     = 0;

    static public $processStatuses = [
        self::STATUS_PENDING    => 'Отложен',
        self::STATUS_NEW        => 'Новый заказ',
        self::STATUS_PROCESSING => 'В обработке',
        self::STATUS_COLLECTED  => 'Собран',
        self::STATUS_DELIVERY   => 'В доставке',
        self::STATUS_COMPLETE   => 'Завершен',
        self::STATUS_CANCELED   => 'Отменен',
        self::STATUS_ABANDONED  => 'Не завершен',
        self::STATUS_PROBLEM    => 'Проблемы',
        self::STATUS_RETURN     => 'Возврат',
        self::STATUS_WAITING    => 'В ПВЗ',
    ];

    static public $paidStatuses = [
        self::STATUS_PAID       => 'Оплачен',
        self::STATUS_UNPAID     => 'Не оплачен',
    ];

    public function __construct()
    {
        $this->setTable('orders');

        $this->addProperties(array(
            'user_id'     => [],
            'city_id'     => [],
            'phone_id'    => [],
            'adwords_id'  => [],
            'status'      => ['default' => self::STATUS_NEW],
            'price'       => [],
            'income'      => [],
            'outgo'       => [],
            'sl_id'       => [],
            'delivery_company'  => [],
            'delivery_income'   => [],
            'delivery_outgo'    => [],
            'paid'        => [],
            'description' => [],
            'time_create' => [],
        ));

        $this->addPlugin('city', function($model) {
            $city = new City();
            $city->setId($model->get('city_id'));

            return $city;
        }, array('independent' => true));

        $this->addPlugin('phone', function($model) {
            $phone = new Phone();
            $phone->setId($model->get('phone_id'));

            return $phone;
        }, ['independent' => true]);

        $this->addPlugin('adwords', function($model) {
            $adwords = new Adwords();
            $adwords->setId($model->get('adwords_id'));

            return $adwords;
        }, ['independent' => true]);

        $this->addPlugin('cart', function($model) {
            $cart = EntityFactory::collection(new \CatalogAdmin\Model\Cart());
            $cart->select()->where(['order_id' => $model->getId()]);

            return $cart;
        });

        $this->addPlugin('attrs', function() {
            $props = new \Aptero\Db\Plugin\Attributes();
            $props->setTable('orders_attributes');

            return $props;
        });

        $this->addPlugin('user', function($model) {
            $user = new \UserAdmin\Model\User();
            $user->setId($model->get('user_id'));

            return $user;
        }, array('independent' => true));
    }

    public function setStatus($status)
    {
        if(!$this->load()) {
            return false;
        }

        switch ($status) {
            case Orders::STATUS_DELIVERY:
                if($this->get('status') != Orders::STATUS_PROCESSING) {
                    return false;
                }

                $this->set('status', Orders::STATUS_DELIVERY);

                (new Task())->setVariables([
                    'task_id'       => Task::TYPE_ORDER_DELIVERY,
                    'item_id'       => $this->getId(),
                    'name'          => 'Заказ отправлен в доставку',
                    'duration'      => 15,
                ])->save();

                break;
            case Orders::STATUS_COMPLETE:
                if($this->get('status') != Orders::STATUS_DELIVERY) {
                    return false;
                }

                $this->set('status', Orders::STATUS_COMPLETE);
                break;
            case Orders::STATUS_CANCELED:
                if($this->get('status') != Orders::STATUS_PROCESSING) {
                    return false;
                }
                $this->set('status', Orders::STATUS_CANCELED);
                $this->clearCart($this->getId());

                break;
            case Orders::STATUS_RETURN:
                if($this->get('status') != Orders::STATUS_DELIVERY) {
                    return false;
                }

                $this->set('status', Orders::STATUS_RETURN);

                /*(new Task())->setVariables([
                    'task_id'       => Task::TYPE_ORDER_RETURN,
                    'item_id'       => $this->getId(),
                    'name'          => 'Обработка возврата',
                    'duration'      => 15,
                ])->save();

                $this->getCallcenterService()->addCall([
                    'type_id'    => Call::TYPE_RETURN,
                    'item_id'    => $this->getId(),
                    'phone_id'   => $this->get('phone_id'),
                    'name'       => $this->getPlugin('attrs')->get('name'),
                    'theme'      => 'Не забрали заказ',
                    'desc'       => '',
                ]);

                $this->clearCart($this->getId());*/

                break;
            default:
                throw new \Exception('Unknown order status');
        }
    }

    /**
     * @return Point
     * @throws \Aptero\Exception\Exception
     */
    public function getPickupPoint()
    {
        $point = new Point();
        $point->setId($this->getPlugin('attrs')->get('point'));
        return $point;
    }

    public function getCity()
    {
        $city = new City();
        $city->setId($this->get('city_id'));

        return $city;
    }

    public function getUser()
    {
        $user = new User();
        $user->setId($this->get('user_id'));

        return $user;
    }
    
    public function getProfit()
    {
        return $this->get('income') - $this->get('outgo') + $this->get('delivery_income') - $this->get('delivery_outgo');
    }

    public function isPaid()
    {
        return $this->get('paid') ? (($this->getPrice() - $this->get('paid')) < 5) : false;
    }

    public function getPrice()
    {
        return $this->get('income') + $this->get('delivery_income');
    }

    public function getDeliveryAddress()
    {
        $attrs = $this->getPlugin('attrs');

		if($street = $attrs->get('street')) {
			$street = '' . $attrs->get('street');
		}

		if($house = $attrs->get('house')) {
			$house = ', д. ' . $attrs->get('house');
		}

		if($building = $attrs->get('building')) {
			$building = ', корп. ' . $building;
		}

		if($flat = $attrs->get('flat')) {
			$flat = ', кв/оф ' . $attrs->get('flat');
		}
		
        return $street . $house . $building . $flat;
    }

    public function getEditUrl()
    {
        return '/admin/catalog/orders/edit/?id=' . $this->getId();
    }

    public function getPublicId()
    {
        return '348-' . $this->getId();
    }
}