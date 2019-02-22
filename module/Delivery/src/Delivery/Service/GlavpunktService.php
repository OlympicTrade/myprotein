<?php

namespace Delivery\Service;

use Aptero\Debug\Debug;
use Aptero\Service\AbstractService;
use Zend\Db\Sql\Expression;
use Zend\Json\Json;
use GuzzleHttp\Client as HttpClient;
use Aptero\Yandex\Client as YaClient;

class GlavpunktService extends AbstractService
{
    const TABLE_REGIONS = 'delivery_regions';
    const TABLE_CITIES  = 'delivery_cities';
    const TABLE_POINTS  = 'delivery_points';
    
    public $fullUpdate = true;

    public function fullUpdate()
    {
        $sql = $this->getSql();

        $sql->getAdapter()->getDriver()->getConnection()->beginTransaction();

        $this->execute($sql->update(self::TABLE_CITIES)->set(['status' => 0]));
        $this->execute($sql->update(self::TABLE_REGIONS)->set(['status' => 0]));

        echo
            '<h2>Города:</h2>'.
            '<h3>Доставка:</h3>'.
            $this->updateCitiesAndRegions('courier').
            '<h3>Самовывооз:</h3>'.
            $this->updateCitiesAndRegions('pickup');

        $sql->getAdapter()->getDriver()->getConnection()->commit();
        $sql->getAdapter()->getDriver()->getConnection()->beginTransaction();

        $this->execute($sql->update(self::TABLE_POINTS)->set(['status' => 0]));

        echo
            '<h2>Точки самовывоза:</h2>'.
            '<h3>Москва и СПб:</h3>'.
            $this->updatePoints(true).
            '<h3>Регионы:</h3>'.
            $this->updatePoints(false);

        $sql->getAdapter()->getDriver()->getConnection()->commit();
        $sql->getAdapter()->getDriver()->getConnection()->beginTransaction();

        echo
            '<h2>Цены:</h2>'.
            '<h3>Доставка:</h3>'.
            $this->updateCourierPrice().
            '<h3>Самовывооз:</h3>'.
            $this->updatePickupPrice().
            $this->updateCitiesData().
            $this->updateRegionsData().
            $this->updatePointsData().
            '<br>';

        $sql->getAdapter()->getDriver()->getConnection()->commit();

        return;

        /*$this->getSql()->getAdapter()->getDriver()->getConnection()->beginTransaction();

        echo
            $this->updateYandexData().
            '<br>';

        $this->getSql()->getAdapter()->getDriver()->getConnection()->commit();*/
    }

    protected function getRegionId($name)
    {
        $name = (string) $name;
        $sql = $this->getSql();

        $select = $sql->select(self::TABLE_REGIONS);
        $select->columns(['id'])->where(['name' => (string) $name]);

        $result = $this->execute($select)->current();

        $data = [
            'name'     => $name,
            'status'   => 1,
            'priority' => (in_array($name, ['Санкт-Петербург', 'Москва']) ? 100 : 0),
        ];

        if(!$result) {
            $this->execute($sql->insert(self::TABLE_REGIONS)->values($data));
            $regionId = $sql->getAdapter()->getDriver()->getLastGeneratedValue();
        } else {
            $regionId = $result['id'];
            $this->execute($sql->update(self::TABLE_REGIONS)->set($data)->where(['id' => $regionId]));
        }

        return $regionId;
    }

    protected function getCityId($name)
    {
        $sql = $this->getSql();

        $select = $sql->select(self::TABLE_CITIES);
        $select->columns(['id'])->where(['name' => (string) $name]);

        $result = $this->execute($select)->current();

        return $result ? $result['id'] : false;

        /*$data = [
            'name'   => $name,
            'status' => 1,
        ];

        if(!$result) {
            $this->execute($sql->insert(self::TABLE_CITIES)->values($data));
            $cityId = $sql->getAdapter()->getDriver()->getLastGeneratedValue();
        } else {
            $cityId = $result['id'];
            $this->execute($sql->update(self::TABLE_CITIES)->set($data)->where(['id' => $cityId]));
        }

        return $cityId;*/
    }

    public function updateCitiesAndRegions($type)
    {
        if($type == 'pickup') {
            $data = $this->getData('/api/get_rf_cities');
        } else {
            $data = $this->getData('/api/get_courier_cities');
        }

        $sql = $this->getSql();

        $i = 0; $u = 0;
        foreach ($data as $row) {
            $name = str_replace(['"', '\''], '', ((string) $row->name));
            $code = (string) $row->code;
            $regionName = (string) $row->area;

            if(!$regionName || !$name) {
                continue;
            }

            $delay = array_pop(explode('-', $row->deliveryPeriod));

            $data = [
                'region_id' => $this->getRegionId($regionName),
                'name'      => $name,
                'code'      => (string) $row->code,
                'kladr'     => (int) $row->kladr_code,
                'status'    => 1,
                'priority'  => (in_array($name, ['Санкт-Петербург', 'Москва']) ? 100 : 0),
                'pickup_delay'    => $delay,
                'delivery_delay'  => $delay,
            ];

            $orSelect = $this->getSql()
                ->select(self::TABLE_CITIES)
                ->columns(['id'])
                ->where(['code' => $code]);

            $result = $this->execute($orSelect)->current();

            if(!$result) {
                $this->execute($sql->insert(self::TABLE_CITIES)->values($data));
                $i++;
            } else {
                $this->execute($sql->update(self::TABLE_CITIES)->set($data)->where(['id' => $result['id']]));
                $u++;
            }
        }

        return 'Новых городов: ' . $i  . '<br>Обновлено городов: ' . $u . '<br>';
    }


    public function updatePoints($capitals)
    {
        if($capitals) {
            $data = $this->getData('/punkts.json');
        } else {
            $data = $this->getData('/punkts-rf.json');
        }

        $sql = $this->getSql();

        $i = 0; $u = 0;
        foreach ($data as $row) {
            //if(mb_strtolower($row->country) != 'россия') continue;

            $code = (string) $row->id;
            $city = (string) $row->city;

            if(!$city || !$cityId = $this->getCityId($city)) {
                echo 'Не указан город для точки: ' . $row->address . '<br>';
                continue;
            }

            $address = trim(str_replace(['"', '\''], '', ((string) $row->address)));
            if(!$address) {
                continue;
            }

            $data = [
                'city_id'   => $this->getCityId($city),
                'code'      => $code,
                'address'   => (string) str_replace(['"', '\''], '', ((string) $row->address)),
                'email'     => (string) $row->email,
                'phone'     => (string) $row->phone,
                'city'      => (string) $row->city,
                'worktime'  => (string) $row->work_time,
                'latitude'  => (string) $row->geo_lat,
                'longitude' => (string) $row->geo_lng,
                'status'    => 1,
            ];

            $select = $this->getSql()
                ->select(self::TABLE_POINTS)
                ->columns(['id']);

            $select
                ->where
                    ->equalTo('address', $address)
                    ->or
                    ->equalTo('code', $code);

            $result = $this->execute($select)->current();

            if(!$result) {
                $this->execute($sql->insert(self::TABLE_POINTS)->values($data));
                $i++;
            } else {
                $this->execute($sql->update(self::TABLE_POINTS)->set($data)->where(['id' => $result['id']]));
                $u++;
            }
        }

        return 'Новых точек: ' . $i  . '<br>Обновлено точек: ' . $u . '<br>';
    }

    protected function updatePickupPrice()
    {
        $sql = $this->getSql();

        $select = $sql
            ->select(self::TABLE_POINTS)
            ->columns(['id', 'code', 'city'])
            ->where(['status' => 1, 'price' => 0]);

        $i = 0;
        foreach ($this->execute($select) as $row) {
            $urlPath = '/api/get_tarif?';
            $urlData = [
                'serv'      => (in_array($row['city'], ['Москва', 'Санкт-Петербург']) ? 'выдача' : 'выдача по РФ'),
                'cityFrom'  => ('Санкт-Петербург'),
                'cityTo'    => (string) $row['city'],
                'punktId'   => (string) $row['code'],
                'weight'    => 2,
                'price'     => 2000,
                'paymentType' => 'cash',
            ];

            foreach ($urlData as $key => $val) {
                $urlPath .= $key . '=' . urlencode($val) . '&';
            }

            $resp = $this->getData($urlPath);

            if($resp->result == 'ok') {
                $price = (int) $resp->tarif;
                $delay = array_pop(explode('-', $resp->period));

                $data = [
                    'price' => $price,
                    'delay' => $delay,
                ];
                $i++;
            } else {
                $data = [
                    'price' => 0,
                ];
            }

            $this->execute($sql->update(self::TABLE_POINTS)->set($data)->where(['id' => $row['id']]));
        }

        return 'Точек c доставкой: ' . $i . '<br>';
    }

    protected function updateCourierPrice()
    {
        $sql = $this->getSql();

        $select = $sql
            ->select(self::TABLE_CITIES)
            ->columns(['id', 'name']);

		/*if(!$this->fullUpdate) {
        	$select->where->equalTo('delivery_income', 0);
		}*/

        $i = 0; $u = 0;
        foreach($this->execute($select) as $row) {
            $urlPath = '/api/get_tarif?';
            $urlPath .= http_build_query([
                'serv'      => 'курьерская доставка',
                'cityFrom'  => 'Санкт-Петербург',
                'cityTo'    => $row['name'],
                'weight'    => 2,
                'price'     => 2000,
                'paymentType' => 'cash',
            ]);

            $resp = $this->getData($urlPath);

            if($resp->result == 'ok') {
                $price = (int) $resp->tarif;
                $delay = array_pop(explode('-', $resp->period));

                $data = [
                    'delivery_income' => $price,
                    'delivery_outgo'  => ceil($price / 10) * 10,
                    'delivery_delay'  => $delay,
                ];
                $i++;
            } else {
                $data = [
                    'delivery_income' => 0,
                    'delivery_outgo'  => 0,
                ];
                $u++;
            }

            $this->execute($sql->update(self::TABLE_CITIES)->set($data)->where(['id' => $row['id']]));
        }

        return 'Городов c доставкой: ' . $i . ', цена не найдена ' . $u . '<br>';
    }

    protected function updateCitiesData()
    {
        $sql = $this->getSql();

        $this->execute($sql->update(self::TABLE_CITIES)->set(['status' => 0]));

        $cSelect = $sql
            ->select(self::TABLE_CITIES)
            ->columns(['id', 'name', 'delivery_income']);

        $i = 0;
        foreach ($this->execute($cSelect) as $row) {
            $pSelect = $sql
                ->select(self::TABLE_POINTS)
                ->columns([
                    'count' => new Expression('COUNT(*)'),
                    'price' => new Expression('AVG(price)'),
                    'delay' => new Expression('AVG(delay)'),
                ])
                ->where(['city_id' => $row['id']]);

            $resp = $this->execute($pSelect)->current();

            $data = [
                'pickup_income'  => $resp['price'],
                'pickup_delay'   => $resp['delay'],
                'points'         => $resp['count'],
            ];

            if($resp['price'] || $row['delivery_income']) {
                $i++;
                $data['status'] = 1;
            } else {
                $data['status'] = 0;
            }

            $this->execute($sql->update(self::TABLE_CITIES)->set($data)->where(['id' => $row['id']]));
        }

        return 'Городов с доставкой или самовывозом: ' . $i . '<br>';
    }

    protected function updateRegionsData()
    {
        $sql = $this->getSql();

        $this->execute($sql->update(self::TABLE_REGIONS)->set(['status' => 0]));

        $rSelect = $sql
            ->select(self::TABLE_REGIONS)
            ->columns(['id']);

        $i = 0;
        foreach ($this->execute($rSelect) as $row) {
            $cSelect = $sql
                ->select(self::TABLE_CITIES)
                ->columns([
                    'count' => new Expression('COUNT(*)'),
                ])
                ->where(['region_id' => $row['id']]);

            $cSelect->where
                ->nest()
                    ->greaterThan('pickup_income', 0)
                    ->or
                    ->greaterThan('delivery_income', 0)
                ->unnest();

            $resp = $this->execute($cSelect)->current();

            if($resp['count']) $i++;

            $this->execute($sql->update(self::TABLE_REGIONS)->set([
                'cities'   => $resp['count'],
                'status'   => 1,
            ])->where(['id' => $row['id']]));
        }

        return 'Регионов с доставкой или самовывозом: ' . $i . '<br>';
    }

    protected function updatePointsData()
    {
        $sql = $this->getSql();

        $cSelect = $sql
            ->select(self::TABLE_CITIES)
            ->columns(['id', 'name']);

        $cSelect->where
            ->equalTo('latitude', 0)
            ->equalTo('longitude', 0)
            ->equalTo('status', 1);

        $i = 0;
        foreach ($this->execute($cSelect) as $row) {

            $c2Select = $sql
                ->select('delivery_cities2')
                ->columns(['latitude', 'longitude'])
                ->where(['name' => $row['name']]);

            if($c2Data = $this->execute($c2Select)->current()) {
                $this->execute($sql->update(self::TABLE_CITIES)->set([
                    'latitude'   => $c2Data['latitude'],
                    'longitude'  => $c2Data['longitude'],
                ])->where(['id' => $row['id']]));
                $i++;
                continue;
            }

            try {
                $address = 'Россия, ' . $row['name'];
                $yaData = Json::decode(file_get_contents('https://geocode-maps.yandex.ru/1.x/?format=json&geocode=' . urlencode($address)));
                $coordsStr = $yaData->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos;

                if(empty($coordsStr)) {
                    throw new \Exception();
                }

                $coords = explode(' ', $coordsStr);
            } catch (\Exception $e) {
                echo 'Не найдены координаты для города: ' . $row['name'] . '<br>';
                $this->execute($sql->update(self::TABLE_REGIONS)->set(['status' => 0])->where(['id' => $row['id']]));
                continue;
            }

            $this->execute($sql->update(self::TABLE_CITIES)->set([
                'latitude'   => $coords[1],
                'longitude'  => $coords[0],
            ])->where(['id' => $row['id']]));

            $i++;
        }

        return 'Обновлено координат у городов: ' . $i . '<br>';
    }

    protected function updateYandexData()
    {
        $sql = $this->getSql();

        $yaCliet = YaClient::getInstance()->auth(['redirect' => '/delivery/update-yandex/']);

        $http = new HttpClient([
            'base_uri' => 'https://api.partner.market.yandex.ru/v2',
            'headers' => [
                'Authorization' => 'OAuth oauth_token="' . $yaCliet->getToken() . '", oauth_client_id="' . $yaCliet->getClientId() . '"',
            ]
        ]);

        //Cities
        /*$cSelect = $sql
            ->select(self::TABLE_CITIES)
            ->columns(['id', 'name']);
        $cSelect->where
            ->equalTo('ya_reg_id', 0);

        $i = 0;
        foreach ($this->execute($cSelect) as $row) {
            try {
                $resp = $http->get('/regions.json', ['query' => ['name' => $row['name']]])->getBody();
                $data = Json::decode($resp);

                if(empty($data->regions)) {
                    continue;
                }

                $this->execute($sql->update(self::TABLE_CITIES)->set(['ya_reg_id' => $data->regions[0]->id])->where(['id' => $row['id']]));
                $i++;
            } catch (\Exception $e) {
                break;
            }
        }

        return 'Yandex: обновлены данные для ' . $i . ' городов<br>';*/

        //Points
        $pSelect = $sql->select()
            ->from(['t' => self::TABLE_POINTS])
            ->columns(['id', 'city', 'address'])
            ->join(['c' => self::TABLE_CITIES], 'c.id = t.city_id', ['region_id' => 'ya_reg_id']);

        $pSelect->where
            ->notEqualTo('c.ya_reg_id', 0)
            ->equalTo('t.address_data', '');

        $i = 0;
        foreach ($this->execute($pSelect) as $row) {
            try {
                $address = 'Россия, ' . $row['city'] . ', ' . $row['address'];
                $yaData = Json::decode(file_get_contents('https://geocode-maps.yandex.ru/1.x/?format=json&geocode=' . urlencode($address)));

                if(!$yaData || !$yaData->response->GeoObjectCollection->featureMember) {
                    continue;
                }

                $data = ['region_id' => $row['region_id']];

                foreach($yaData->response->GeoObjectCollection->featureMember[0]->GeoObject->metaDataProperty->GeocoderMetaData->Address->Components as $component) {
                    switch ($component->kind) {
                        case 'street':
                            $data['street'] = $component->name;
                            break;
                        case 'house':
                            $data['house'] = $component->name;
                            break;
                    }
                }

                $this->execute($sql->update(self::TABLE_POINTS)->set(['address_data' => Json::encode($data)])->where(['id' => $row['id']]));
                $i++;
            } catch (\Exception $e) {
                echo 'Не найдены координаты для точки: ' . $row['id'] . ' Адрес: ' . $address . '<br>';
                continue;
            }
        }

        return 'Yandex: обновлены данные адресов для ' . $i . ' точек<br>';
    }

    protected function getData($path)
    {
        $url = 'http://glavpunkt.ru' . $path;

        try {
            return Json::decode(file_get_contents($url));
        } catch (\Exception $e) {
            return (object) ['result' => 'error', 'message' => 'HTTP request failed!'];
        }
    }
}