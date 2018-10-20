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
    }

    /**
     * @param $date
     * @return \DateTime
     */
    public function getDeliveryDate()
    {
        $date = new \DateTime();
        $date->modify('+' . $this->get('delay') . ' days');

        /*$orderDate = \DateTime::createFromFormat('Y-m-d H:i:s', $order->get('time_create'));
        $deliveryDate = $this->getDeliveryService()->getDeliveryDates(['orderDate' => $orderDate], 'pickup');

        $daysStr = [
            1   => 'в понедельник',
            2   => 'во вторник',
            3   => 'в среду',
            4   => 'в четверг',
            5   => 'в пятницу',
            6   => 'в субботу',
            7   => 'в воскресенье',
        ];

        $messages[] = 'Заказ будет доставлен ' . $daysStr[$deliveryDate->format('N')] . ' в 15:00. При поступлении на точку выдачи вы получите SMS сообщение. До этого момента заказ не счтаеться привезенным';

        $point = new Pickup();
        $point->setId($attrs->get('point'));*/

        return $date;
    }
}