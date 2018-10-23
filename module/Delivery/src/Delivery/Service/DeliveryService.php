<?php

namespace Delivery\Service;

use Application\Model\Region;
use Aptero\Delivery\RussianPost;
use Aptero\Service\AbstractService;
use Delivery\Model\City;
use Delivery\Model\Delivery;
use Delivery\Model\Pickup;
use Delivery\Model\Point;

class DeliveryService extends AbstractService
{
    public function getCitiesData()
    {
        $center = ['lat' => 0, 'lon' => 0];
        $points = [];
        $cities = City::getEntityCollection();
        $cities->select()
            ->where
                ->notEqualTo('points', 0);

        $citiesCount = $cities->count();

        if(!$citiesCount) {
            return [
                'points' => $points,
                'center' => $center,
            ];
        }

        foreach ($cities as $city) {
            $html =
                '<div class="point-desc">'
                    .'<div class="title">' . $city->get('name') . '</div>'
                    .'<div class="row">Срок доставки: ' . $city->getDeliveryDelay() . ' ' . \Aptero\String\Numbers::declension($city->getDeliveryDelay(), ['день', 'дня', 'дней']) . '</div>';

            if($city->get('pickup_income')) {
                $html .= '<div class="row">Стоимость самовывоза: ' . $city->get('pickup_income') . ' руб.</div>';
            }

            if($city->get('delivery_income')) {
                $html .= '<div class="row">Стоимость досавки: ' . $city->get('delivery_income') . ' руб.</div>';
            }

            $html .=
                    '<div class="row btns"><span class="btn s chose-city" data-name="' . $city->get('name') . '" data-id="' . $city->getId() . '">Выбрать регион доставки</span></div>'
                .'</div>';

            $html = str_replace(["\n", "\r"], '', $html);

            $points [] = [
                'lat' => $city->get('latitude'),
                'lon' => $city->get('longitude'),
                'desc' => $html
            ];

            $center['lat'] += $city->get('latitude');
            $center['lon'] += $city->get('longitude');
        }

        $center['lat'] /= $citiesCount;
        $center['lon'] /= $citiesCount;

        return [
            'points' => $points,
            'center' => $center,
        ];
    }

    public function getPointsData($data)
    {
        $city = Delivery::getInstance()->getCity();

        $center = ['lat' => 0, 'lon' => 0];
        $points = [];
        $pointsCol = $city->getPlugin('points');

        /*if (in_array($city->getId(), City::$indexPickupCities)) {
            $pointsCol->select()->where(['company' => Delivery::COMPANY_INDEX_EXPRESS]);
        } else {
            $pointsCol->select()->where(['company' => Delivery::COMPANY_SHOP_LOGISTIC]);
        }*/

        if(isset($data['pid']) && $pointId = $data['pid']) {
            $pointsCol->select()->where(['id' => $pointId]);
        }

        $pointsCount = $pointsCol->count();
        
        if(!$pointsCount) {
            return [
                'points' => $points,
                'center' => $center,
            ];
        }

        foreach ($pointsCol as $point) {
            $html =
                '<div class="marker-box point-desc">';

            $images = $point->getPlugin('images');

            if($images->count()) {
                $html .=
                    '<div class="info">';
            }

            $html .=
                 ($point->get('metro') ? '<div class="row"><i class="fas fa-train"></i> м. ' . $point->get('metro') . '</div>' : '')
                .($point->get('phone') ? '<div class="row"><i class="fas fa-phone"></i>' . $point->get('phone') . '</div>' : '')
                .'<div class="row"><i class="far fa-clock"></i>' . str_replace("\n", ',', $point->get('worktime')) . '</div>'
                .'<div class="row"><i class="fas fa-map-marker-alt"></i>' . $point->get('address') . '</div>'
                .($point->get('route') ? '<div class="row route">' . $point->get('route') . '</div>' : '')
                ;

            if(!isset($data['type']) || $data['type'] != 'view') {
                $html .=
                    '<div class="row btns"><span class="btn s chose-point" data-id="' . $point->getId() . '">Выбрать точку самовывоза</span></div>';
            }

            if($images->count()) {
                $html .=
                    '</div>'
                    .'<div class="gallery popup-gallery ' . ($images->count() < 4 ? 'g-3' : 'g-4') . '">';

                $i = 0;
                foreach ($images as $image) {
                    $html .=
                        '<a href="' . $image->getImage('hr') . '" ' . ($i < 4 ? '' : 'style="display: none"') . '>'
                            .'<img src="' . $image->getImage('a') . '">'
                        .'</a>';

                    $i++;
                }

                $html .=
                    '</div>';
            }



            $html .=
                '</div>';

            $html = str_replace(["\n", "\r"], '', $html);

            $points [] = [
                'lat' => $point->get('latitude'),
                'lon' => $point->get('longitude'),
                'desc' => $html
            ];

            $center['lat'] += $point->get('latitude');
            $center['lon'] += $point->get('longitude');
        }
        
        $center['lat'] /= $pointsCol->count();
        $center['lon'] /= $pointsCol->count();

        return [
            'points' => $points,
            'center' => $center,
        ];
    }

    public function getDeliveryPrice($options = [], $type = null)
    {
        $result = [];
        
        if(!isset($options['price'])) {
            $price = $this->getCartService()->getCartPrice();
        } else {
            $price = $options['price'];
        }

		if(!empty($options['cityId'])) {
			$city = new City();
			$city->setId($options['cityId']);
		} else {
			$city = Delivery::getInstance()->getCity();
		}
		
        $packagePrice = 10;

        if(($type === null || $type == 'pickup')) {
            $isFree = $city->getFreeDeliveryPrice(['type' => 'pickup']) < $price;

            $result['pickup'] = [
                'income' => $isFree ? 0 : $city->get('pickup_income'),
                'outgo' => $city->get('pickup_outgo') + $packagePrice,
            ];
        }
        
        if(($type === null || $type == 'courier')) {
            $isFree = $city->getFreeDeliveryPrice(['type' => 'delivery']) < $price;
            $result['courier'] = [
                'income' => $isFree ? 0 : $city->get('delivery_income'),
                'outgo' => $city->get('delivery_outgo') + $packagePrice,
            ];
        }
        
        if($type === null || $type == 'post') {
            $isFree = false;
            $post = new RussianPost();
            $cartService = $this->getCartService();
            $region = Region::getInstance();

            $price = $post->getPrice([
                'price' => $cartService->getCartPrice(),
                'weight' => $cartService->getCartPrice(),
                'to_index' => $region->get('index'),
            ]);

            $result['post'] = [
                'income' => $isFree ? 0 : $price,
                'outgo' => (!$isFree ? 0 : $price) + $packagePrice,
            ];
        }

        if(isset($options['callback'])) {
            array_walk($result, $options['callback']);
        }

        if($type !== null) {
            return $result[$type];
        }

        return $result;
    }

    /**
     * @param array $options
     * @param null $type
     * @return array|\DateTime
     */
    public function getDeliveryDates($options = [], $type = null)
    {
        $result = [];

        if($type === null || $type == 'pickup') {
            $result['pickup'] = $this->getNearestPickupDate($options);
        }

        if($type === null || $type == 'courier') {
            $result['courier'] = $this->getNearestCourierDate($options);
        }

        if($type === null || $type == 'post') {
            $result['post'] = $this->getNearestPostDate($options);
        }

        if(isset($options['callback'])) {
            array_walk($result, $options['callback']);
        }

        if($type !== null) {
            return $result[$type];
        }

        return $result;
    }

    public function getCourierExcludedDays()
    {
        $excludedDates = [];
		
		$cityName = Delivery::getInstance()->getCity()->get('name');
		
		if($cityName == 'Москва') {
			return $excludedDates;
		}
		
        $start = (new \DateTime());
        $end = (new \DateTime())->modify('+1 month');

        $interval = new \DateInterval('P1D');

        $period = new \DatePeriod($start, $interval, $end);

        foreach ($period as $day) {
            if($day->format('N') == 7) {
                $excludedDates[] = $day->format('d.m.Y');
            }
        }

        return $excludedDates;
    }

    public function getPickupCount($order)
    {
        $points = Point::getEntityCollection();
        $points->select()->where(['city_id' => $order->get('city_id')]);

        return $points->count();
    }

    public function getNearestPickupDate($options)
    {
        $pickupDate = isset($options['orderDate']) ? $options['orderDate'] : new \DateTime();
        $deliveryDelay = Delivery::getInstance()->getCity()->getDeliveryDelay(['date' => $pickupDate, 'type' => 'pickup']);
		
        if($options['format'] == 'delay') {
            return $deliveryDelay;
        }

        return $pickupDate->modify('+ ' . $deliveryDelay . ' days');
    }

    protected $courierDate = null;
    public function getNearestCourierDate($options)
    {
        $courierDate = isset($options['orderDate']) ? $options['orderDate'] : new \DateTime();
        $deliveryDelay = Delivery::getInstance()->getCity()->getDeliveryDelay(['date' => $courierDate, 'type' => 'courier']);

        if($options['format'] == 'delay') {
            return $deliveryDelay;
        }

        return $courierDate->modify('+ ' . $deliveryDelay . ' days');
    }

    public function getNearestPostDate($options = [])
    {
        $post = new RussianPost();

        if(!$options) {
            $cartService = $this->getCartService();
            $options = [
                'price'     => $cartService->getCartPrice(),
                'weight'    => $cartService->getCartWeight(),
                'to_index'  => Region::getInstance()->get('index'),
            ];
        }
        
        return $post->getDate($options);
    }
    
    /**
     * @return \Catalog\Service\CartService
     */
    protected function getCartService()
    {
        return $this->getServiceManager()->get('Catalog\Service\CartService');
    }
}