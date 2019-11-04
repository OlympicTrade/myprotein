<?php
namespace Delivery\Model;

use Application\Model\Region;
use Aptero\Db\Entity\Entity;

class Delivery extends Entity
{
    const TYPE_EXPRESS = 'express';
    const TYPE_COURIER = 'courier';
    const TYPE_PICKUP  = 'pickup';
    const TYPE_POST    = 'post';

    static public $deliveryTypes = [
        self::TYPE_EXPRESS  => 'Экспресс доставка',
        self::TYPE_COURIER  => 'Курьерская доставка',
        self::TYPE_PICKUP   => 'Самовывоз',
        self::TYPE_POST     => 'Почта России',
    ];

    const COMPANY_INDEX_EXPRESS = 1;
    const COMPANY_SHOP_LOGISTIC = 2;
    const COMPANY_RUSSIAN_POST  = 3;
    const COMPANY_GLAVPUNKT     = 4;
    const COMPANY_UNKNOWN       = 10;

    static public $deliveryCompanies = [
        self::COMPANY_INDEX_EXPRESS  => 'Индекс Экспресс',
        self::COMPANY_SHOP_LOGISTIC  => 'Shop Logistic',
        self::COMPANY_RUSSIAN_POST   => 'Почта России',
        self::COMPANY_GLAVPUNKT      => 'Главпункт',
        self::COMPANY_UNKNOWN        => 'Не выбрана',
    ];
    
    static public $instance;

    protected $data = [
        'city'    => null,
        'region'  => null,
        'points'  => null,
    ];

    /**
     * @return City
     */
    public function getCity()
    {
        if(!$this->data['city']) {
            $this->data['city'] = (new City())->loadFromIp()->load();
        }

        return $this->data['city'];
    }

    static public function getInstance()
    {
        if(!self::$instance) {
            $delivery = new self();
            self::$instance = $delivery;
        }

        return self::$instance;
    }
}