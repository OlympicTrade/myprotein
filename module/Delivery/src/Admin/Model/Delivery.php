<?php
namespace DeliveryAdmin\Model;

use ApplicationAdmin\Model\Region;
use Aptero\Db\Entity\Entity;

class Delivery extends Entity
{
    const TYPE_COURIER = 'courier';
    const TYPE_PICKUP  = 'pickup';
    const TYPE_POST    = 'post';

    static public $deliveryTypes = [
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

    public function __construct()
    {
        $this->setTable('delivery');

        $this->addProperties(array(
            'region_id'       => [],
            'name'            => [],
            'delay'           => [],
            'pickup_text'     => [],
            'pickup_income'   => [],
            'pickup_outgo'    => [],
            'pickup_free'     => [],
            'courier_income'  => [],
            'courier_outgo'   => [],
            'courier_free'    => [],
            'delivery_text'   => [],
            'delay_text'      => [],
            'courier_company' => [],
        ));

        $this->addPlugin('regions', function($model) {
            $catalog = new Region();
            $catalog->setId($model->get('catalog_id'));

            return $catalog;
        }, array('independent' => true));
    }
}