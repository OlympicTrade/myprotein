<?php
namespace DeliveryAdmin\Model;

use Aptero\Db\Entity\Entity;
use Zend\Db\Sql\Expression;
use Zend\Session\Container;

class City extends Entity
{
    public function __construct()
    {
        $this->setTable('delivery_cities');

        $this->addProperties([
            'region_id'       => [],
            'name'            => [],
            'delivery_income' => [],
            'delivery_outgo'  => [],
            'delivery_delay'  => [],
            'pickup_income'   => [],
            'pickup_delay'    => [],
            'code'            => [],
            'latitude'        => [],
            'longitude'       => [],
            'priority'        => [],
        ]);

        $this->addPlugin('points', function($model) {
            $points = Point::getEntityCollection();
            $points->select()->where(['city_id' => $model->getId()]);

            return $points;
        });
    }
    
    public function getFreeDeliveryPrice()
    {
        return ceil(max($this->get('delivery_income'), $this->get('pickup_income'), 300) / 100) * 1000;
    }

    public function loadFromIp()
    {
        $session = new Container();

        if($session->offsetExists('region')) {
            $this->unserialize($session->region);

            if($_COOKIE['region'] == $this->getId()) {
                return $this;
            }
            $this->clear();
        }

        if(!empty($_COOKIE['region'])) {
            $this->select()->where(['id' => $_COOKIE['region']]);
            if($this->load()) {
                $session->region = $this->serialize();
                return $this;
            }
        }

        //Detect Lat and Lon
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
            //$sxData = $SxGeo->getCityFull('178.219.186.12'); //СПб
            $sxData = $SxGeo->getCityFull('188.191.19.242'); // Севастополь
        }

        $this->select()
            ->order(new Expression('ABS(t.longitude - ' . $sxData['city']['lon'] . ' + t.latitude - ' . $sxData['city']['lat'] . ')'))
            ->where
                ->notEqualTo('t.points', 0)
                ->notEqualTo('t.latitude', 0)
                ->notEqualTo('t.longitude', 0);


        $session->region = $this->serialize();
        $_COOKIE['region'] = $this->getId();

        return $this;
    }
}