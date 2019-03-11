<?php
namespace CatalogAdmin\Model;

use Aptero\Db\Entity\Entity;
use Aptero\Db\Entity\EntityHierarchy;
use BalanceAdmin\Model\BalanceFlow;
use ManagerAdmin\Model\Task;

class Supplies extends Entity
{
    const STATUS_NEW      = 0;
    const STATUS_COMPLETE = 5;

    const CURRENCY_RUB    = 0;
    const CURRENCY_EUR    = 1;
    const CURRENCY_USD    = 2;

    const MYPROTEIN_RATE = 70;

    static public $statuses = [
        self::STATUS_NEW      => 'Новый заказ',
        self::STATUS_COMPLETE => 'Заказ получен',
    ];

    static public $currency = [
        self::CURRENCY_RUB    => 'Рубли',
        self::CURRENCY_EUR    => 'Евро',
        self::CURRENCY_USD    => 'Доллары',
    ];

    static public $users = [
        7   => 'Рогожин Виктор Александрович',
        8   => 'Рогожина Ольга Дмитриевна',
        33  => 'Гурина Галина Васильевна',
        34  => 'Цветкова Наталья Владимировна',
        35  => 'Жукова Марина Владимировна',

        /*10  => 'Васильев Александр Владимирович',
        1   => 'Васильев Константин Сергеевич',
        13  => 'Васильев Павел Евгеньевич',
        2   => 'Васильев Сергей Николаевич',
        3   => 'Васильева Любовь Леонидовна',
        17  => 'Власов Павел Сергеевич',
        22	=> 'Гунько Андрей Борисович',
        23	=> 'Гунько Андрей Васильевич',
        20  => 'Иванов Александр Михайлович',
        21  => 'Иванов Дмитрий Михайлович',
        18  => 'Иванов Дмитрий Константинович',
        24	=> 'Иванов Вячеслав Сергеевич',
        25	=> 'Иванов Дмитрий Иванович',
        26	=> 'Иванов Дмитрий Михайлович',
        27	=> 'Иванов Кирилл Владимирович',
        28	=> 'Иванов Федор Владимирович',
        33  => 'Кузнецов Сергей Вячеславович',
        15  => 'Кац Анна Семенова',
        4   => 'Кукин Роман Олегович',
        5   => 'Кукина Татьяна Павловна',
        6   => 'Кустов Станислав Александрович',
        11  => 'Кустов Игорь Владимирович',
        12  => 'Кустов Виталий Анатольевич',
        16  => 'Лопухов Виталий Сергеевич',
        19  => 'Павлов Вячеслав Александрович',
        7   => 'Рогожин Виктор Александрович',
        8   => 'Рогожина Ольга Дмитриевна',
        9   => 'Семенов Игорь Викторович',
        14  => 'Смирнов Александр Сергеевич',
        29	=> 'Соколов Александр Юрьевич',
        30	=> 'Соколов Андрей Борисович',
        31	=> 'Соколов Дмитрий Олегович',
        32	=> 'Соколов Сергей Юрьевич',*/
    ];

    public function __construct()
    {
        $this->setTable('supplies');

        $this->addProperties([
            'user_id'       => [],
            'balance_id'    => [],
            'login'         => [],
            'number'        => [],
            'date'          => ['default' => date('Y-m-d')],
            'weight'        => ['default' => 15],
            'price'         => [],
            'currency_rate' => [],
            'delivery'      => [],
            'desc'          => [],
            'status'        => ['default' => self::STATUS_NEW],
        ]);

        $this->addPlugin('cart', function($model) {
            $cart = SuppliesCart::getEntityCollection();
            $cart->select()->where(array('supply_id' => $model->getId()));

            return $cart;
        });

        /*
        $this->getEventManager()->attach(array(Entity::EVENT_PRE_INSERT), function ($event) {
            $model = $event->getTarget();

            $balance = new BalanceFlow();
            $balance->setVariables([
                'desc'      => 'Заказ №' . $model->get('number'),
                'price'     => -1 * $model->get('price'),
                'type'      => BalanceFlow::TYPE_SUPPLIES,
                'date'      => date('Y-m-d'),
            ]);
            $balance->save();

            $model->set('balance_id', $balance->getId());

            return true;
        });*/

        $this->getEventManager()->attach(array(Entity::EVENT_POST_INSERT), function ($event) {
            $model = $event->getTarget();

            (new Task())->setVariables([
                'task_id'       => Task::TYPE_SUPPLY_NEW,
                'item_id'       => $model->getId(),
                'name'          => 'Заявка на закупку товаров',
                'duration'      => 30,
            ])->save();

            return true;
        });

        /*
        $this->getEventManager()->attach(array(Entity::EVENT_PRE_UPDATE), function ($event) {
            $model = $event->getTarget();

            $balance = new BalanceFlow();
            $balance->setId($model->get('balance_id'));

            if($balance->load()) {
                $balance->setVariables([
                    'desc'      => 'Заказ №' . $model->get('number'),
                    'price'     => -1 * $model->get('price'),
                    'type'      => BalanceFlow::TYPE_SUPPLIES,
                    'date'      => date('Y-m-d'),
                ]);

                $model->set('balance_id', $balance->getId());
            } else {
                $balance->set('price', $model->get('price'));
            }

            $balance->save();

            return true;
        });*/

        /*
        $this->getEventManager()->attach(array(Entity::EVENT_PRE_DELETE), function ($event) {
            $model = $event->getTarget();

            $balance = new BalanceFlow();
            $balance->setId($model->get('balance_id'))->remove();
        });*/
    }

    protected $margin = [];

    public function getDelivery($currency = self::CURRENCY_EUR)
    {
        return $this->getCurrency($this->get('delivery'), $currency);
    }
    /*
    public function getDiscount($currency = self::CURRENCY_EUR)
    {
        if(!isset($this->margin['discount'])) {
            $this->margin['discount'] =  min(($this->getPrice() / (100 - $this->get('discount')) * 100) - $this->getPrice(), 100);
        }
        return $this->getCurrency($this->margin['discount'], $currency);
    }

    public function getFullPrice($currency = self::CURRENCY_EUR)
    {
        return  $this->getCurrency($this->get('price'), $currency);
    }

    public function getCurrencyMargin()
    {
        return round($this->getPrice() * (self::MYPROTEIN_RATE - $this->get('currency_rate')));
    }

    public function getMargin()
    {
        return round($this->getCurrencyMargin() - $this->getDelivery(self::CURRENCY_RUB));
    }

    public function getMarginPercent()
    {
        if(! (int) $this->get('currency_rate')) { return ' - '; }
        return round($this->getMargin() / $this->getFullPrice(self::CURRENCY_RUB) * 100);
    }
    */

    public function getPrice($currency = self::CURRENCY_EUR)
    {
        $price = $this->get('price') - $this->get('delivery');
        return  $this->getCurrency($price, $currency);
    }
    
    public function getCurrency($sum, $currency)
    {
        switch ($currency) {
            case self::CURRENCY_EUR:
                return round($sum, 2);
                break;
            case self::CURRENCY_RUB:
                return round($this->get('currency_rate') * $sum);
                break;
        }

        throw new \Exception('Unknown currency: ' . $currency);
    }
}