<?php

namespace Catalog\Service;

use Application\Model\Settings;
use Aptero\Service\AbstractService;
use Catalog\Model\Cart;
use Catalog\Model\Catalog;
use Catalog\Model\Order;
use Catalog\Model\Product;
use Delivery\Model\City;
use Delivery\Model\Delivery;
use Zend\Json\Json;

class YandexMarket extends AbstractService
{
    protected $token = '38000001E0616CCC';

    public function confirmCart()
    {
        if($ck = $this->checkToken()) {
            return $ck;
        }

        try {
            $query = file_get_contents('php://stdin');
            $json = Json::decode($query);
        } catch (\Exception $e) {
            return $this->send404();
        }

        $cart = $json->cart;

        if($cart->currency != 'RUR') {
            header("HTTP/1.0 404 Not Found");
            die('Unknown currency');
        }

        $city = new City();
        $city->select()->where(['name' => $cart->delivery->region->name]);
        if(!$city->load()) {
            die('City error');
        }

        $items = [];
        $totalPrice = 0;
        foreach ($cart->items as $item) {
            list($productId, $sizeId, $tasteId) = explode('S', $item->offerId);

            $product = $this->getProductsService()->getProduct([
                'id'        => $productId,
                'size_id'   => $sizeId,
                'taste_id'  => $tasteId,
            ]);

            $items[] = [
                'count'    => $product->get('stock'),
                'delivery' => true,
                'feedId'   => $item->feedId,
                'offerId'  => $item->offerId,
                'price'    => $product->get('price'),
                'vat'      => 'NO_VAT'
            ];

            $totalPrice += $product->get('price');
        }

        $deliveryOptions = [];

        $deliveryOptions[] = [
            'id' => Delivery::TYPE_POST,
            'paymentAllow' => 'true',
            'price' => ($city->getFreeDeliveryPrice(['type' => 'delivery']) > $totalPrice ? $city->get('post_income') : 0),
            'serviceName' => Delivery::$deliveryCompanies[Delivery::COMPANY_RUSSIAN_POST],
            'type' => 'POST',
            'vat' => 'NO_VAT',
            'paymentMethods' => [
                'YANDEX',
            ],
            'dates' => [
                'fromDate'  => (new \DateTime())->modify('+' . $city->getDeliveryDelay(['type' => 'post']) . ' days')->format('d-m-Y'),
            ]
        ];

        if($city->get('delivery_income')) {
            $intervals = [];

            $delay = $city->getDeliveryDelay(['type' => 'delivery']);
            $dateFrom = (new \DateTime())->modify('+' . $delay . ' days');
            $dateTo = (new \DateTime())->modify('+' . ($delay + 7) . ' days');

            $period = new \DatePeriod($dateFrom, \DateInterval::createFromDateString('1 day'), (clone $dateTo)->modify('+1 day'));

            foreach ($period as $dt) {
                if(in_array($dt->format('N'), $city->getDeliveryExcludedWeekdays())) {
                    continue;
                }

                $date = $dt->format('d-m-Y');
                foreach ($city->getDeliveryTimePeriods() as $period) {
                    $intervals[] = [
                        'date'     => $date,
                        'fromTime' => $period['from'],
                        'toTime'   => $period['to'],
                    ];
                }
            }

            $deliveryOptions[] = [
                'id' => Delivery::TYPE_COURIER,
                'paymentAllow' => 'true',
                'price' => ($city->getFreeDeliveryPrice(['type' => 'delivery']) > $totalPrice ? $city->get('delivery_income') : 0),
                'serviceName' => Delivery::$deliveryCompanies[$city->detectDeliveryCompany($city->getId(), new \DateTime(), 'delivery')],
                'type' => 'DELIVERY ',
                'vat' => 'NO_VAT',
                'paymentMethods' => [
                    'YANDEX',
                    'CASH_ON_DELIVERY',
                ],
                'dates' => [
                    'fromDate'  => $dateFrom->format('d-m-Y'),
                    'toDate'    => $dateTo->format('d-m-Y'),
                    'intervals' => $intervals,
                ]
            ];
        }

        //if($city->get('pickup_income')) {}

        $resp = [
            'deliveryCurrency' => 'RUR',
            'taxSystem'        => 'USN',
            'items'            => $items,
            'deliveryOptions'  => $deliveryOptions,
        ];

        return $resp;
    }

    public function acceptOrder()
    {
        if($ck = $this->checkToken()) {
            return $ck;
        }

        try {
            $query = file_get_contents('php://stdin');
            $json = Json::decode($query);
        } catch (\Exception $e) {
            return $this->send404();
        }

        $query = $json['order'];

        if($query->currency != 'RUR') {
            header("HTTP/1.0 404 Not Found");
            die('Unknown currency');
        }

        $order = new Order();
        $desc = '';

        $order->setVariables([
            'status'    => Order::STATUS_NEW,
        ]);

        $order->save();

        $attrs = $order->getPlugin('attrs');

        switch ($query->delivery->type) {
            case 'DELIVERY': $attrs->set('delivery', Delivery::TYPE_COURIER); break;
            case 'PICKUP': $attrs->set('delivery', Delivery::TYPE_PICKUP); break;
            case 'POST': $attrs->set('delivery', Delivery::TYPE_POST); break;
        }

        $city = new City();
        $city->select()->where(['name' => $query->delivery->region->name]);
        if(!$city->load()) {
            $desc .= 'Неизвестный регион: ' . $query->delivery->region->name . ' ' . $query->delivery->region->parent->name . "\n";
        } else {
            $order->set('city_id', $city->getId());
        }

        if($query->delivery->address) {
            $address = $query->delivery->address;
            $date = $query->delivery->dates;

            $attrs->setVariables([
                'index'     => $address->postcode,
                'house'     => $address->delivery,
                'street'    => $address->street,
                'building'  => $address->block,
                'date'      => $date->fromDate,
                'time_from' => $date->fromTime,
                'time_to'   => $date->toTime,
            ]);
        }

        foreach ($query->items as $item) {
            list($productId, $sizeId, $tasteId) = explode('S', $item->offerId);

            $product = $this->getProductsService()->getProduct([
                'id'        => $productId,
                'size_id'   => $sizeId,
                'taste_id'  => $tasteId,
            ]);

            $cart = new Cart();
            $cart->setVariables([
                'order_id'    => $order->getId(),
                'product_id'  => $product->getId(),
                'size_id'     => $product->getId('size_id'),
                'taste_id'    => $product->getId('taste_id'),
                'order_count' => $product->get('stock'),
                'price'       => $product->get('price'),
            ])->save();
        }

        $order->set('description', $desc);

        $order->save();

        $resp = [
            'order' =>  [
                'accepted' => true,
                'id' => $order->getId(),
            ]
        ];

        return $resp;
    }

    public function checkToken()
    {
        if($_GET['auth-token'] == $this->token) {
            return ['error' => 'token'];
        }
        return false;
    }

    /**
     * @return \Catalog\Service\ProductsService
     */
    protected function getProductsService()
    {
        return $this->getServiceManager()->get('Catalog\Service\ProductsService');
    }
}