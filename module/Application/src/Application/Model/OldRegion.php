<?php
namespace Application\Model;

use Aptero\Db\Entity\Entity;
use Zend\Db\Sql\Expression;
use Zend\Session\Container;

class Region extends Entity
{
    static protected $instance;

    public function __construct()
    {
        $this->setTable('regions')
            ->enableCache()
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
        $session = new Container();

        

        /*$region = new self();
        $sx = $region->getSxGeoInfo();
        echo
            $sx['city']['lat'] . ' - '
            .$sx['city']['lon'] . '<br><br><br>';

        $select = $region->getSql()->select();
        $select->from(['dc' => 'delivery_cities'])
            ->order(new Expression('ABS(dc.longitude - ' . $sx['city']['lon'] . ' + dc.latitude - ' . $sx['city']['lat'] . ')'))
            ->limit(10)
            ->where
                ->notEqualTo('dc.latitude', 0)
                ->notEqualTo('dc.longitude', 0);

        $result = $region->execute($select);

        foreach ($result as $row) {
            echo
             $row['full_name'] . '<br>'
            .abs($row['latitude'] - $sx['city']['lat'] + $row['longitude'] - $sx['city']['lon']) . '<br>'
            .$row['latitude'] . ' - '
            .$row['longitude'] . '<br><br>';
        }

        die();*/

        if(!self::$instance) {
            $region = new self();

            if($session->offsetExists('region') && $_COOKIE['region'] == $region->getId()) {
                $region->unserialize($session->region);
            } else {
                if(!empty($_COOKIE['region'])) {
                    $region->select()->where(['id' => $_COOKIE['region']]);
                } else {
                    $sx = $region->getSxGeoInfo();
                    $region->select()->where(['name' => $sx['region']['name_ru']]);
                }

                if(!$region->load()) {
                    $region->clearSelect();
                    $region->select()->where(['name' => 'Москва']);
                    $region->load();
                    $region->set('city', 'Не найден');
                    setcookie('region', null);
                } else {
                    $session->region = $region->serialize();
                    $_COOKIE['region'] = $region->getId();
                }
            }

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

        $ip = '178.219.186.12';
        return $SxGeo->getCityFull($ip);
    }
}