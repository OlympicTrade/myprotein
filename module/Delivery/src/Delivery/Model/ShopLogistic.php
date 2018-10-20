<?php
namespace Delivery\Model;

class ShopLogistic
{
    public function getError($reqType, $code)
    {
        $errors = [
            0 => '',
            1 => 'Ошибка подключения (клиент не найден)',
            2 => 'Ошибка подключения (не существующая функция)',
        ];

        switch ($reqType) {
            case 'add_delivery':
                $errors = $errors + [
                    4 => 'Неверная дата или доставка недоступна для обновления',
                    5 => 'Доставка обновлена',
                ];
                break;
            case 'add_zabor':
                $errors = $errors + [
                    43 => 'Выбранная дата уже закрыта',
                    44 => 'Забор на выбранную дату уже есть в базе',
                ];
                break;
        }
        
        return $errors[(string) $code];
    }

    public function getClientCode($forBarcode = true)
    {
        return $forBarcode ? '0626' : 'SPB000626';
    }

    public function getBarcode($orderId)
    {
        return
              '00626' //client code
            . date('my') // month year
            . str_pad(substr($orderId, -4), 4, '0', STR_PAD_LEFT) //order id 4 digits
            . '2'; //filial (SPb);
    }
    
    public function getXml($reqType)
    {
        $api = 'f6e43fc84e8044cd12597045e7ea2d63';
        //$api = '577888574a3e4df01867cd5ccc9f18a5'; //test

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><request></request>');
        $xml->addChild('function', $reqType);
        $xml->addChild('api_id', $api);

        return $xml;
    }

    public function getData(\SimpleXMLElement $xml, $dump = false)
    {
        //header('Content-Type: text/xml');
        //die($xml->asXML());

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'http://client-shop-logistics.ru/index.php?route=deliveries/api');
        //curl_setopt($curl, CURLOPT_URL, 'https://test.client-shop-logistics.ru/index.php?route=deliveries/api'); //test
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