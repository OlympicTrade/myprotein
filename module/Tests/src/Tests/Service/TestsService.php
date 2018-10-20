<?php

namespace Tests\Service;

use Aptero\Service\AbstractService;
use CatalogAdmin\Model\Orders;
use Delivery\Model\City;
use Delivery\Model\Point;
use Zend\Barcode\Barcode;
use Zend\Barcode\Object;
use Zend\Barcode\Renderer;
use Zend\Db\Sql\Expression;
use Zend\Stdlib\DateTime;

class TestsService extends AbstractService
{
    const TABLE_REGIONS = 'delivery_regions';
    const TABLE_CITIES  = 'delivery_cities';
    const TABLE_POINTS  = 'delivery_points';

    protected $errorCodes = [
        43 => 'Выбранная дата уже закрыта',
        44 => 'Забор на выбранную дату уже есть в базе',
    ];

    protected $pointTypes = ['А', 'Б'];

    public function sendOrders()
    {
        $orders = Orders::getEntityCollection();
        $orders->select()->where(['id' => 1729]);

        $xml = $this->getXml('add_delivery');
        $diliveries = $xml->addChild('deliveries');

        foreach ($orders as $order) {
            $delivery = $xml->addChild('delivery');

            $delivery->addChild('project_key', $order->getId());
            $delivery->addChild('code', '');

            $attrs = $order->getPlugin('attrs');

            $delivery->addChild('delivery_date', $attrs->get('date'));
            $delivery->addChild('date_transfer_to_store', $attrs->get('send_date'));
            $delivery->addChild('from_city', '958281');
            $delivery->addChild('to_city', '958281');
        }

        die();

        $resp = $this->getData($xml, true);

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

    public function requestCourier($date, $placeCode = '')
    {
        $xml = $this->getXml('add_zabor');
        $item = $xml->addChild('zabors')->addChild('zabor');
        $item->addChild('zabor_places_code', $placeCode);
        $item->addChild('delivery_date', $date);
        
        $resp = $this->getData($xml, true);

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

    public function barcode()
    {
        $renderer = Barcode::factory(
            'code128',
            'image',
            ['text' => '08555011300003'],
            ['imageType' => 'png'],
            $automaticRenderError = true
        );

        $image = $renderer->draw();

        imagepng($image, PUBLIC_DIR . '/engine/barcodes/test.png', 9);

        die();
    }

    public function updatePointPrice()
    {
        $step = 100;
        $file = __DIR__ . '/counter.txt';
        $firstId = (int) file_get_contents($file);

        $cities = City::getEntityCollection();
        $cities->select()
            ->offset($firstId)
            ->limit($step);

        $i = 0;
        foreach($cities as $city) {
            $i++;
            $this->updatePriceListPoints($city);
        }

        if(!$i) {
            file_put_contents($file, 0);
            return $this->updatePointPrice();
        }
        
        file_put_contents($file, $firstId + $step);

        return '';
    }

    public function updatePriceListPoints(City $city)
    {
        $xml = $this->getXml('get_deliveries_tarifs');
        $xml->addChild('from_city', '958281'); //СПб
        $xml->addChild('to_city', $city->get('code'));
        $xml->addChild('weight', '1');
        $xml->addChild('order_length', '');
        $xml->addChild('order_width', '');
        $xml->addChild('order_height', '');
        $xml->addChild('order_price', '2500');
        $xml->addChild('ocen_price', '1500');
        $xml->addChild('num', '100');

        $rows = $this->getData($xml);

        foreach ($rows->tarifs->tarif as $row) {
            if($row->tarifs_type == 1) {
                $city->setVariables([
                    'delivery_income'  => $row->price,
                    'delivery_outgo'   => $row->price,
                    'delivery_delay'   => $row->srok_dostavki,
                ])->save();
                continue;
            }

            $point = new Point();
            $point->select()->where(['code' => (string) $row->pickup_place_code]);

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
        $xml = $this->getXml('get_dictionary');
        $xml->addChild('dictionary_type', 'pickup');
        $xml->addChild('pickup_places_type', $pointsType);

        $rows = $this->getData($xml, true);
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
                'time_update'   => date('Y-m-d H:i:s'),
                'payment_cards'      => (string) $row->payment_cards,
                'receiving_orders'   => (string) $row->receiving_orders,
                'partial_redemption' => (string) $row->partial_redemption,
            ];

            $orSelect = $sql->select(self::TABLE_POINTS);
            $orSelect
                ->columns(['id'])
                ->where(['name' => (string) $row->name]);

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
            ->lessThanOrEqualTo('time_update', (new \DateTime())->modify('-5 minutes')->format('Y-m-d H:i:s'))
            ->equalTo('type', $pointsType);
        $this->execute($delete);

        return 'Новых точек: ' . $i  . '<br>Обновлено точек: ' . $u . '<br><br>';
    }
    
    public function updatePointsCount()
    {
        //Cities
        $sql = $this->getSql();
        $select = $sql->select(self::TABLE_CITIES);
        $select->columns(['id']);

        $c = 0;
        foreach ($this->execute($select) as $city) {
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
                    ->notEqualTo('points', 0);

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
        $xml = $this->getXml('get_dictionary');
        $xml->addChild('dictionary_type', 'city');

        $rows = $this->getData($xml);
        $sql = $this->getSql();

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
                'time_update'   => date('Y-m-d H:i:s'),
            ];

            $orSelect = $this->getSql()->select(self::TABLE_CITIES);
            $orSelect
                ->columns(['id'])
                ->where(['full_name' => $fullName]);

            $result = $this->execute($orSelect)->current();

            if(!$result) {
                $this->execute($sql->insert(self::TABLE_CITIES)->values($data));
                $i++;
            } else {
                $this->execute($sql->update(self::TABLE_CITIES)->set($data)->where(['id' => $result['id']]));
                $u++;
            }
        }
        
        $delete = $this->getSql()->delete(self::TABLE_CITIES);
        $delete->where->lessThanOrEqualTo('time_update', (new \DateTime())->modify('-5 minutes')->format('Y-m-d H:i:s'));
        $this->execute($delete);

        return 'Новых городов: ' . $i  . '<br>Обновлено городов: ' . $u . '<br><br>';
    }

    public function updateRegions()
    {
        $xml = $this->getXml('get_dictionary');
        $xml->addChild('dictionary_type', 'oblast');

        $rows = $this->getData($xml);
        $sql = $this->getSql();

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
                ->where(['name' => $name]);

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
        
        return 'Новых регионов: ' . $i  . '<br>Обновлено регионов: ' . $u . '<br><br>';
    }

    protected function getXml($reqType)
    {
        //$api = 'f6e43fc84e8044cd12597045e7ea2d63';
        $api = '577888574a3e4df01867cd5ccc9f18a5'; //test

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><request></request>');
        $xml->addChild('function', $reqType);
        $xml->addChild('api_id', $api);

        return $xml;
    }

    protected function getData(\SimpleXMLElement $xml, $dump = false)
    {
        //header('Content-Type: text/xml');
        //die($xml->asXML());

        $curl = curl_init();
        //curl_setopt($curl, CURLOPT_URL, 'http://client-shop-logistics.ru/index.php?route=deliveries/api');
        curl_setopt($curl, CURLOPT_URL, 'https://test.client-shop-logistics.ru/index.php?route=deliveries/api'); //test
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, 'xml=' . urlencode(base64_encode($xml->asXML())));
        $resp = curl_exec($curl);
        curl_close($curl);

        if($dump) {
            header('Content-Type: text/xml');
            die((new \SimpleXMLElement($resp))->asXML());
        }

        return  new \SimpleXMLElement($resp);
    }
}