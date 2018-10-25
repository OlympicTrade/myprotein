<?php
namespace Delivery\Model;

use Application\Model\Region;
use Aptero\Db\Entity\Entity;

class Point extends Entity
{
    public function __construct()
    {
        $this->setTable('delivery_points');

        $this->addProperties([
            'city_id'           => [],
            'name'              => [],
            'address'           => [],
            'route'             => [],
            'type'              => [],
            'price'             => [],
            'phone'             => [],
            'worktime'          => [],
            'delay'             => [],
            'code'              => [],
            'latitude'          => [],
            'longitude'         => [],
            'index_express'     => [],
            'glavpunkt'         => [],
        ]);

        $this->addPlugin('images', function() {
            $image = new \Aptero\Db\Plugin\Images();
            $image->setTable('delivery_points_images');
            $image->setFolder('points');
            $image->addResolutions([
                'a' => [
                    'width'  => 120,
                    'height' => 150,
                    'crop'   => true
                ],
                'hr' => [
                    'width'  => 900,
                    'height' => 900,
                ],
            ]);

            return $image;
        });

        $this->addPlugin('city', function($model) {
            $city = new City();
            $city->setId($model->get('city_id'));

            return $city;
        });
    }

    /**
     * @param $date
     * @return \DateTime
     */
    public function getDeliveryDate()
    {
        $delay = $this->getPlugin('city')->getDeliveryDelay(['type' => Delivery::TYPE_PICKUP]);
        $dt = (new \DateTime())->modify('+' . $delay . ' days');

        return $dt;
    }
}