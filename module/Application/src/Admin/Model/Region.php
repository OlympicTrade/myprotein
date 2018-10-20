<?php
namespace ApplicationAdmin\Model;

use Aptero\Db\Entity\Entity;
use DeliveryAdmin\Model\Delivery;

class Region extends Entity
{
    static protected $instance;

    public function __construct()
    {
        $this->setTable('regions')
            ->addProperties([
                'name'   => [],
                'short'  => [],
                'index'  => [],
            ]);
    }

    /**
     * @return Region
     */
    static public function getInstance()
    {
        if(!self::$instance) {
            $region = new self();

            if(isset($_COOKIE['region'])) {
                $sxRegion = $_COOKIE['region'];
            } else {
                $sx = $region->getSxGeoInfo();
                $sxRegion = $sx ? $sx['region']['name_ru'] : '';
            }

            switch($sxRegion) {
                case 'Москва':
                    $sxRegion = 'Москва';
                    break;
                default:
                    $sxRegion = 'Санкт-Петербург';
                    break;
            }

            $region->select()->where(array('sxgeo' => $sxRegion));
            $region->load();

            self::$instance = $region;
        }

        return self::$instance;
    }

    public function getSxGeoInfo()
    {
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

        return $SxGeo->getCityFull($ip);
    }
}