<?php
namespace CallcenterAdmin\Model;

use Aptero\Db\Entity\Entity;
use CatalogAdmin\Model\Orders;
use CatalogAdmin\Model\Requests;
use UserAdmin\Model\Phone;

class Call extends Entity
{
    const TYPE_ORDER   = 10;
    const TYPE_REQUEST = 20;
    const TYPE_RETURN  = 30;
    const TYPE_OTHER   = 40;

    const STATUS_NEW      = 0;
    const STATUS_COMPLETE = 1;
    const STATUS_REJECT   = 2;
    
    static public $types = [
        self::TYPE_ORDER    => 'Проблемы с заказом',
        self::TYPE_REQUEST  => 'Запрос на товар',
        self::TYPE_RETURN   => 'Не забрали заказ',
        self::TYPE_OTHER    => 'Прочее',
    ];

    static public $statuses = [
        self::STATUS_NEW        => 'Новая заявка',
        self::STATUS_COMPLETE   => 'Выполнена',
        self::STATUS_REJECT     => 'Отклонена',
    ];

    public function __construct()
    {
        $this->setTable('callcenter');

        $this->addProperties([
            'type_id'       => [],
            'item_id'       => [],
            'phone_id'      => [],
            'name'          => [],
            'theme'         => [],
            'desc'          => [],
            'status'        => [],
            'time_create'   => [],
        ]);

        $this->addPlugin('item', function($model) {
            switch ($model->get('type_id')) {
                case self::TYPE_REQUEST: $item = new Requests(); break;
                case self::TYPE_ORDER: $item = new Orders(); break;
                case self::TYPE_RETURN: $item = new Orders(); break;
                default: return null;
            }

            $item->setId($model->get('item_id'));

            return $item;
        }, ['independent' => true]);

        $this->addPlugin('phone', function($model) {
            $phone = new Phone();
            $phone->setId($model->get('phone_id'));

            return $phone;
        }, ['independent' => true]);
    }

    public function getEditUrl()
    {
        return '/admin/callcenter/callcenter/edit/?id=' . $this->getId();
    }
}