<?php
namespace Delivery\Model;

use Application\Model\Region;
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

    public function getPickupCount()
    {
        $points = Pickup::getEntityCollection();
        $points->select()->where(['delivery_id' => $this->getId()]);
        return $points->count();
    }

    public function getNearestPickupDate()
    {
        $pickupDate = new \DateTime();

        switch ($pickupDate->format('N')) {
            case 1: $deliveryDelay = 1; break;
            case 5:
                $deliveryDelay = $pickupDate->format('H') < 17 ? 1 : 3;
                break;
            case 6: $deliveryDelay = 2; break;
            case 7: $deliveryDelay = 1; break;
            default:
                $deliveryDelay = $pickupDate->format('H') < 17 ? 1 : 2;
                break;
        }

        $delivery = \Delivery\Model\Delivery::getInstance();
        $deliveryDelay += $delivery->get('delay');

        return $pickupDate->modify('+ ' . $deliveryDelay . ' days');
    }

    public function getNearestCourierDate()
    {
        $courierDate = new \DateTime();

        switch ($courierDate->format('N')) {
            case 1: $deliveryDelay = 1; break;
            case 5:
                $deliveryDelay = $courierDate->format('H') < 17 ? 1 : 3;
                break;
            case 6: $deliveryDelay = 2; break;
            case 7: $deliveryDelay = 1; break;
            default:
                $deliveryDelay = $courierDate->format('H') < 17 ? 1 : 2;
                break;
        }

        $delivery = \Delivery\Model\Delivery::getInstance();
        $deliveryDelay += $delivery->get('delay');

        return $courierDate->modify('+' . $deliveryDelay . ' days');
    }

    public function getNearestPostDate()
    {
        
    }
}