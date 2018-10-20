<?php

namespace Delivery\Service;

use Aptero\Service\AbstractService;
use Delivery\Model\City;
use Delivery\Model\Delivery;
use Delivery\Model\Point;
use Delivery\Model\ShopLogistic;
use Zend\Db\Sql\Expression;

class ShopLogisticService extends AbstractService
{
    const TABLE_REGIONS = 'delivery_regions';
    const TABLE_CITIES  = 'delivery_cities';
    const TABLE_POINTS  = 'delivery_points';

    protected $errorCodes = [
        43 => 'Выбранная дата уже закрыта',
        44 => 'Забор на выбранную дату уже есть в базе',
    ];

    protected $pointTypes = ['А'];
    
    protected $sync;
    public function getSyncMd()
    {
        if(!$this->sync) {
            $this->sync = new ShopLogistic();
        }
        
        return $this->sync;
    }

    public function requestCourier($date, $placeCode = '')
    {
        $xml = $this->getSyncMd()->getXml('add_zabor');
        $item = $xml->addChild('zabors')->addChild('zabor');
        $item->addChild('zabor_places_code', $placeCode);
        $item->addChild('delivery_date', $date);
        
        $resp = $this->getSyncMd()->getData($xml, true);

        $status = [
            'status' => true,
            'error' => ''
        ];

        $error = $resp->zabors->zabor->error_code;
        if($error != 0) {
            $status['status'] = false;
            $status['error'] = $this->errorCodes[$error];
        }

        return $status;
    }

    public function updatePointPrice()
    {
        $step = 200;
        $file = __DIR__ . '/counter.txt';
        $firstId = (int) file_get_contents($file);

        $cities = City::getEntityCollection();
        $cities->select()
            ->limit($step);

        $cities->select()->where
            //->equalTo('id', 4821)
            ->greaterThan('id', $firstId)
            ->nest()
                ->notEqualTo('points', 0)
                ->or
                ->notEqualTo('is_courier', 0)
            ->unnest();

        $lastId = 0;
        $i = 0;
        foreach($cities as $city) {
            $i++;
            $this->updatePriceListPoints($city);
            $lastId = $city->getId();
        }

        if(!$lastId) {
            file_put_contents($file, 0);
            return $this->updatePointPrice();
        }
        
        file_put_contents($file, $lastId);

        return 'Обновлено цен в городах: ' . $i . '<br><br>';
    }

    public function updatePriceListPoints(City $city)
    {
        $xml = $this->getSyncMd()->getXml('get_deliveries_tarifs');
        $xml->addChild('from_city', '958281'); //СПб
        $xml->addChild('to_city', $city->get('code'));
        $xml->addChild('weight', '1');
        $xml->addChild('order_length', '');
        $xml->addChild('order_width', '');
        $xml->addChild('order_height', '');
        $xml->addChild('order_price', '2500');
        $xml->addChild('ocen_price', '1500');
        $xml->addChild('num', '100');

        $rows = $this->getSyncMd()->getData($xml);

        foreach ($rows->tarifs->tarif as $row) {
            if($row->tarifs_type == 1) {
                if(in_array($city->get('name'), ['Санкт-Петербург'])) {
                    $city->setVariables([
                        'delivery_income'  => 200,
                        'delivery_outgo'   => 300,
                        'delivery_delay'   => 1,
                    ])->save();
                } elseif(in_array($city->get('name'), ['Москва'])) {
                    $city->setVariables([
                        'delivery_income'  => 200,
                        'delivery_outgo'   => 300,
                        'delivery_delay'   => 1,
                    ])->save();
                } else {
					$city->setVariables([
						'delivery_income'  => $row->price,
						'delivery_outgo'   => $row->price,
						'delivery_delay'   => $row->srok_dostavki,
					])->save();
				}
                continue;
            }

            $point = new Point();
            $point->select()->where([
                'code'    => (string) $row->pickup_place_code,
                'company' => Delivery::COMPANY_SHOP_LOGISTIC]);

            if(!$point->load()) {
                continue;
            }

            $point->setVariables([
                'price' => (string) $row->price,
                'delay' => (string) $row->srok_dostavki,
            ])->save();
        }

        return '';
    }

    public function updatePickupPoints($pointsType)
    {
        $xml = $this->getSyncMd()->getXml('get_dictionary');
        $xml->addChild('dictionary_type', 'pickup');
        $xml->addChild('pickup_places_type', $pointsType);

        $rows = $this->getSyncMd()->getData($xml);
        $sql = $this->getSql();

        $i = 0; $u = 0;
        foreach ($rows->pickups->pickup as $row) {
            if(!in_array($row->pickup_places_type, $this->pointTypes)) {
                continue;
            }

            $select = $sql->select(self::TABLE_CITIES);
            $select->columns(['id'])
                ->where(['code' => (string) $row->city_code_id]);

            $city = $this->execute($select)->current();

            $data = [
                'city_id'       => (string) $city['id'],
                'name'          => (string) $row->name,
                'type'          => (string) $row->pickup_places_type,
                'address'       => (string) $row->address,
                'route'         => (string) $row->proezd_info,
                'phone'         => (string) $row->phone,
                'worktime'      => (string) $row->worktime,
                'delay'         => (string) $row->srok_dostavki,
                'code'          => (string) $row->code_id,
                'latitude'      => (string) $row->latitude,
                'longitude'     => (string) $row->longitude,
                'clothes'       => (string) $row->trying_on_clothes,
                'shoes'         => (string) $row->trying_on_shoes,
                'city'          => (string) $row->city_name,
                'company'       => Delivery::COMPANY_SHOP_LOGISTIC,
                'time_update'   => date('Y-m-d H:i:s'),
                'payment_cards'      => (string) $row->payment_cards,
                'receiving_orders'   => (string) $row->receiving_orders,
                'partial_redemption' => (string) $row->partial_redemption,
            ];

            $orSelect = $sql->select(self::TABLE_POINTS);
            $orSelect
                ->columns(['id'])
                ->where([
                    'name'    => (string) $row->name,
                    'company' => Delivery::COMPANY_SHOP_LOGISTIC,
                ]);

            $result = $this->execute($orSelect)->current();

            if(!$result) {
                $this->execute($sql->insert(self::TABLE_POINTS)->values($data));
                $i++;
            } else {
                $this->execute($sql->update(self::TABLE_POINTS)->set($data)->where(['id' => $result['id']]));
                $u++;
            }
        }

        $delete = $this->getSql()->delete(self::TABLE_POINTS);
        $delete->where
            ->lessThanOrEqualTo('time_update', (new \DateTime())->modify('-45 minutes')->format('Y-m-d H:i:s'))
            ->equalTo('type', $pointsType)
            ->equalTo('company', Delivery::COMPANY_SHOP_LOGISTIC);
        $this->execute($delete);

        return 'Новых точек: ' . $i  . '<br>Обновлено точек: ' . $u . '<br><br>';
    }
    
    public function updatePointsCount()
    {
        //Cities
        $sql = $this->getSql();
        $select = $sql->select(self::TABLE_CITIES);
        $select->columns(['id', 'name'])->where
            ->nest()
                ->notEqualTo('points', 0)
                ->or
                ->notEqualTo('is_courier', 0)
            ->unnest();

        $c = 0;
        foreach($this->execute($select) as $city) {
            $select = $this->getSql()->select(self::TABLE_POINTS);
            $select->columns([
                'points' => new Expression('COUNT(*)'),
                'price'  => new Expression('AVG(price)'),
                'delay'  => new Expression('MIN(delay)'),
            ])
            ->where(['city_id' => $city['id']]);

            $result = $this->execute($select)->current();

            $update = $sql->update(self::TABLE_CITIES)
                ->set([
                    'points'        => (int) $result['points'],
                    'pickup_income' => (int) $result['price'],
                    'pickup_delay'  => (int) $result['delay'],
                ])->where(['id' => $city['id']]);
            $this->execute($update);

            $c += $result['points'] ? 1 : 0;
        }

        //Regions
        $select = $sql->select(self::TABLE_REGIONS);
        $select->columns(['id']);
        $r = 0;
        foreach ($this->execute($select) as $region) {
            $select = $this->getSql()->select(self::TABLE_CITIES);
            $select->columns(['count' => new Expression('COUNT(*)')])
                ->where
                    ->equalTo('region_id', $region['id'])
                    ->nest()
                        ->notEqualTo('points', 0)
                        ->or
                        ->notEqualTo('is_courier', 0)
                    ->unnest();

            $count = $this->execute($select)->current()['count'];

            $update = $sql->update(self::TABLE_REGIONS)
                ->set(['cities' => $count])
                ->where(['id' => $region['id']]);
            $this->execute($update);

            $r += $count ? 1 : 0;
        }

        return 'Гордов с доставкой: ' . $c  . '<br>Регионов с доставкой: ' . $r . '<br><br>';
    }

    public function updateCities()
    {
        $this->getSql()->getAdapter()->getDriver()->getConnection()->beginTransaction();

        $xml = $this->getSyncMd()->getXml('get_dictionary');
        $xml->addChild('dictionary_type', 'city');

        $rows = $this->getSyncMd()->getData($xml);
        $sql = $this->getSql();

        $delete = $this->getSql()->delete(self::TABLE_CITIES);
        $delete->where->lessThanOrEqualTo('time_update', (new \DateTime())->modify('-2 days')->format('Y-m-d H:i:s'));
        $this->execute($delete);

        $i = 0; $u = 0;
        foreach ($rows->cities->city as $row) {
            $select = $this->getSql()->select(self::TABLE_REGIONS);
            $select->columns(['id'])
                ->where(['code' => (string) $row->oblast_code]);

            $region = $this->execute($select)->current();

            $fullName = trim($row->name);
            $name = (string) $row->name;
            $name = str_replace(['"', '\''], '', $name);

            if(strpos($name, ',')) {
                $name = substr($name, 0, strpos($name, ','));
            }

            $name = trim($name);

            $priority = in_array($name, ['Санкт-Петербург', 'Москва']) ? 100 : 0;

            $data = [
                'region_id'     => (string) $region['id'],
                'name'          => $name,
                'full_name'     => $fullName,
                'code'          => (string) $row->code_id,
                'is_courier'    => (string) $row->is_courier,
                'is_filial'     => (string) $row->is_filial,
                'shoplogistic'  => (string) $row->is_shoplogistics,
                'kladr'         => (string) $row->kladr_code,
                'latitude'      => (string) $row->latitude,
                'longitude'     => (string) $row->longitude,
                'priority'      => (int) $priority,
            ];

            $orSelect = $this->getSql()->select(self::TABLE_CITIES);
            $orSelect
                ->columns(['id'])
                ->where(['code' => (string) $row->code_id]);

            $result = $this->execute($orSelect)->current();

            if(!$result) {
                $this->execute($sql->insert(self::TABLE_CITIES)->values($data + ['status' => 1]));
                $i++;
            } else {
                $this->execute($sql->update(self::TABLE_CITIES)->set($data)->where(['id' => $result['id']]));
                $u++;
            }
        }

        $this->getSql()->getAdapter()->getDriver()->getConnection()->commit();

        return 'Новых городов: ' . $i  . '<br>Обновлено городов: ' . $u . '<br><br>';
    }

    public function updateRegions()
    {
        $this->getSql()->getAdapter()->getDriver()->getConnection()->beginTransaction();

        $xml = $this->getSyncMd()->getXml('get_dictionary');
        $xml->addChild('dictionary_type', 'oblast');

        $rows = $this->getSyncMd()->getData($xml);
        $sql = $this->getSql();

        $delete = $sql->delete(self::TABLE_REGIONS);
        $delete->where->lessThan(
            'time_update',
            (new \DateTime())->modify('-2 days')->format('Y-m-d H:i:s')
        );
        $this->execute($delete);

        $u = 0; $i = 0;
        foreach ($rows->oblast_list->oblast as $row) {
            $name = trim($row->name);
            $priority = in_array($name, ['Санкт-Петербург', 'Москва']) ? 100 : 0;

            $data = [
                'name'        => $name,
                'code'        => (string) $row->code,
                'priority'    => (int) $priority,
                'time_update' => date('Y-m-d H:i:s'),
            ];

            $orSelect = $this->getSql()->select(self::TABLE_REGIONS);
            $orSelect
                ->columns(['id'])
                ->where(['code' => (string) $row->code]);

            $result = $this->execute($orSelect)->current();

            if(!$result) {
                $this->execute($sql->insert(self::TABLE_REGIONS)->values($data));
                $i++;
            } else {
                $this->execute($sql->update(self::TABLE_REGIONS)->set($data)->where(['id' => $result['id']]));
                $u++;
            }
        }

        $delete = $this->getSql()->delete(self::TABLE_REGIONS);
        $delete->where->lessThanOrEqualTo('time_update', (new \DateTime())->modify('-5 minutes')->format('Y-m-d H:i:s'));
        $this->execute($delete);

        $this->getSql()->getAdapter()->getDriver()->getConnection()->commit();

        return 'Новых регионов: ' . $i  . '<br>Обновлено регионов: ' . $u . '<br><br>';
    }
}