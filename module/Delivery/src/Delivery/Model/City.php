<?php
namespace Delivery\Model;

use Aptero\Db\Entity\Entity;
use Aptero\Delivery\RussianPost;
use Catalog\Model\Order;
use Zend\Db\Sql\Expression;
use Zend\Json\Json;
use Zend\Session\Container;

class City extends Entity
{
    static public $maxDeliveryPrice = 700;

    protected $mscId = '3381';
    protected $spbId = '4798';

    public function __construct()
    {
        $this->setTable('delivery_cities');

        $this->addProperties([
            'name'            => [],
            'region_id'       => [],
            'delivery_income' => [],
            'delivery_outgo'  => [],
            'delivery_delay'  => [],
            'pickup_income'   => [],
            'pickup_outgo'    => ['virtual' => true],
            'pickup_delay'    => [],
            'post_income'     => [],
            'post_outgo'      => ['virtual' => true],
            'post_delay'      => [],
            'index'           => [],
            'code'            => [],
            'latitude'        => [],
            'longitude'       => [],
            'priority'        => [],
        ]);
	
		$this->addPropertyFilterOut('delivery_income', function($model, $val) {
            if($this->isMoscow()) {
                return 300;
            }

            if($this->isSpb()) {
                return 250;
            }

			return round((int) $val / 10) * 10;
		});
	
		$this->addPropertyFilterOut('delivery_outgo', function($model, $val) {
			return (int)$val;
		});
	
		$this->addPropertyFilterOut('pickup_income', function($model, $val) {
            if($this->isMoscow()) {
                return 150;
            }

            if($this->isSpb()) {
                return 100;
            }

			return round((int)$val / 10) * 10;
		});
	
		$this->addPropertyFilterOut('pickup_outgo', function($model) {
			return $model->get('pickup_income');
		});

        $this->addPropertyFilterOut('post_outgo', function($model, $val) {
            return $model->get('post_income');
        });

        $this->addPropertyFilterOut('post_income', function($model, $val) {
            if(!$val) {
                $val = $this->updatePostPrice();
            }
            return $val;
        });

        $this->addPropertyFilterOut('index', function($model, $val) {
            if(!$val) {
                $val = $this->updateIndex();
            }

            return $val;
        });

        $this->addPlugin('points', function($model) {
            $points = Point::getEntityCollection();
            $points->select()->where([
            	'city_id' => $model->getId(),
            	'status'  => 1,
        	]);

            return $points;
        });
    }

    public function getDeliveryDelay($options = [])
    {
        if(!empty($options['date'])) {
            $dt = clone $options['date'];
        } else {
            $dt = new \DateTime();
        }

        switch ($dt->format('N')) {
            case 5:
                $delay = 3;
                break;
            case 6:
                $delay = 2;
                break;
            case 7:
                $delay = 1;
                break;
            default:
                $delay = ($dt->format('H') < 13) ? 0 : 1;
                break;
        }

        switch ($options['type']) {
            case Delivery::TYPE_PICKUP:
                $delay += $this->get('pickup_delay');
                break;
            case Delivery::TYPE_COURIER:
                $delay += $this->get('delivery_delay');
                break;
            default:
                $delay += max($this->get('delivery_delay'), $this->get('pickup_delay'));
                break;
        }

        return $delay;
    }

    public function hasCourier()
    {
        return $this->get('delivery_delay') && $this->get('delivery_income');
    }

    public function hasPickup()
    {
        return $this->get('pickup_delay') && $this->get('pickup_income');
    }

    public function getDeliveryExcludedWeekdays()
    {
        if(in_array($this->get('name'), ['Москва'])) {
            return [];
        }

        return [7];
    }

    public function getDeliveryTimePeriods() {
        $periods = [
            ['from' => '09:00', 'to' => '12:00'],
            ['from' => '12:00', 'to' => '15:00'],
            ['from' => '15:00', 'to' => '18:00'],
        ];

        if($this->isSpb()) {
            $periods[] =
                ['from' => '18:00', 'to' => '21:00'];
        }

        return $periods;
    }

    /**
     * @param Order $order
     * @return int
     */
    public function detectDeliveryCompany($order)
    {
        return Delivery::COMPANY_GLAVPUNKT;

        /*
        Устаревшая тема
        $attrs = $order->getPlugin('attrs');

        if($attrs->get('delivery') == Delivery::TYPE_COURIER) {
            if($order->getPlugin('city')->isCapital()) {
                $company = Delivery::COMPANY_INDEX_EXPRESS;
            } else {
                $company = Delivery::COMPANY_GLAVPUNKT;
            }
        } else {
            $point = $order->getPickupPoint();

            if($point->get('index_express')) {
                $company = Delivery::COMPANY_INDEX_EXPRESS;
            } elseif($point->get('glavpunkt')) {
                $company = Delivery::COMPANY_GLAVPUNKT;
            } else {
                $company = Delivery::COMPANY_UNKNOWN;
            }
        }

        return $company;
        */
    }

    protected function getRussianPostDelay()
    {
        if(!$delay = $this->get('post_delay')) {
            $delay = $this->updatePostDelay();
        }

        return $delay;
    }
/*
    protected function getIndexExpressDelay(\DateTime $dt)
    {
        //Будешь менять Незабудь также обновить Yandex YML!!11one

        switch ($dt->format('N')) {
            case 7:
                $delay = 2;
                break;
            case 6:
                $delay = ($dt->format('H') < 11) ? 2 : 3;
                break;
            default:
                $delay = ($dt->format('H') < 15) ? 1 : 2;
                break;
        }

        return $delay;
    }

    protected function getShopLogisticDelay(\DateTime $dt)
    {
        $delay = $this->get('delivery_delay');
        $delay = max($delay, 1);

        switch ($dt->format('N')) {
            case 7:
                $delay++;
                break;
            case 6:
                $delay += ($dt->format('H') < 10) ? 1 : 2;
                break;
            default:
                $delay = ($dt->format('H') < 15) ? 1 : 2;
                break;
        }

        if($this->get('name') != 'Санкт-Петербург') {
            $delay++;
        }

        $dt->modify('+' . $delay . ' days');
        if($dt->format('N') == 7 && $this->get('name') != 'Москва') {
            $delay++;
        }

        if(!in_array($this->get('name'), ['Москва', 'Санкт-Петербург'])) {
            $delay += 2;
        }

        return $delay;
    }
    */
    public function getFreeDeliveryPrice($options = ['type' => 'delivery'])
    {
        if($options['type'] == 'pickup' && $this->isCapital()) {
            return 3000;
        }

        return ceil(max($this->get('delivery_income'), $this->get('pickup_income'), 300) / 100) * 1000;
    }

    public function isCapital()
    {
        return in_array($this->get('name'), ['Санкт-Петербург', 'Москва']);
    }

    public function isMoscow()
    {
        return in_array($this->get('name'), ['Москва']);
    }

    public function isSpb()
    {
        return in_array($this->get('name'), ['Санкт-Петербург']);
    }

    public function loadFromIp()
    {
        $session = new Container();

        if($session->offsetExists('city') && $session->city->id) {
            $this->unserialize($session->city);

            if($_COOKIE['city'] == $this->getId()) {
                return $this;
            }
            $this->clear();
        }

        if(!empty($_COOKIE['city'])) {
            $this->select()->where(['id' => $_COOKIE['city']]);

            if($this->load()) {
                $session->city = $this->serialize(['serializePlugins' => false]);
                return $this;
            }
        }

        include_once(MAIN_DIR . '/vendor/sxgeo/SxGeo.php');
        $SxGeo = new \SxGeo('SxGeoCity.dat', SXGEO_BATCH | SXGEO_MEMORY);
		
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_X_FORWARDED')) {
            $ip = getenv('HTTP_X_FORWARDED');
        } elseif (getenv('HTTP_FORWARDED_FOR')) {
            $ip = getenv('HTTP_FORWARDED_FOR');
        } elseif (getenv('HTTP_FORWARDED')) {
            $ip = getenv('HTTP_FORWARDED');
        } elseif (getenv('REMOTE_ADDR')) {
            $ip = getenv('REMOTE_ADDR');
        } else {
            $ip = '';
        }

        $sxData = $SxGeo->getCityFull($ip);
        if(!$sxData) {
            $sxData = $SxGeo->getCityFull('178.219.186.12'); //СПб
            //$sxData = $SxGeo->getCityFull('188.191.19.242'); // Севастополь
        }

		$this->clear();
        $this->select()
            ->order(new Expression('ABS(t.longitude - ' . $sxData['city']['lon'] . ' + t.latitude - ' . $sxData['city']['lat'] . ')'))
            ->where
                ->notEqualTo('t.points', 0)
                ->notEqualTo('t.latitude', 0)
                ->notEqualTo('t.longitude', 0);

        $session->city = $this->serialize();
        $_COOKIE['city'] = $this->getId();
        return $this;
    }

    //Post API
    protected $postApiKey = '2j9jh7z6q6s0gybs';

    public function updateIndex()
    {
        try {
            $resp = Json::decode(file_get_contents('http://post-api.ru/api/city2index.php?'
                .'apikey=' . $this->postApiKey
                .'&city=Санкт-Петербург'
            ));
        } catch (\Exception $e) {
            return '';
        }

        $index =  $resp->content[0]->indexes[0];

        $this->set('index', $index)->save();
        return $index;
    }

    public function updatePostPrice()
    {
        if(!$index = $this->get('index')) {
            return 0;
        }

        try {
            $resp = Json::decode(file_get_contents('http://post-api.ru/api/delivcost.php?'
                .'apikey=' . $this->postApiKey
                .'&i=' . $index
                .'&c=1500' //Стоимость
                .'&ac=0' //Дополнительная стоимость? хз что это
                .'&we=1500' //Вес
                .'&w=100' //Ширина
                .'&h=100' //Высота
                .'&de=400' //Длинна
                .'&in=1' //Страховка
                .'&war=0' //Легкобьющийся
                .'&a=0' //Авиадоставка
            ));
            @$price = (int) $resp->content->total_cost;
            @$price = ceil($price / 10) * 10;
        } catch (\Exception $e) {
            return 0;
        }

        $this->set('post_income', $price)->save();

        return $price;
    }

    public function updatePostDelay()
    {
        if(!$index = $this->get('index')) {
            return 0;
        }

        try {
            $resp = Json::decode(file_get_contents('http://post-api.ru/api/postdata.php?'
                .'apikey=' . $this->postApiKey
                .'&i=' . $index
            ));
            $delay = @$resp->DUR->content->topostcenter - 3;
            $delay = max($delay, 3);
        } catch (\Exception $e) {
            return '';
        }

        $this->set('post_delay', $delay)->save();

        return $delay;
    }
}