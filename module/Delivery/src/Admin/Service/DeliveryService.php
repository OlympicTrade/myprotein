<?php
namespace DeliveryAdmin\Service;

use ApplicationAdmin\Model\Settings;
use Aptero\Service\Admin\TableService;
use CatalogAdmin\Model\Orders;
use Delivery\Model\ShopLogistic;
use DeliveryAdmin\Model\Delivery;

class DeliveryService extends TableService
{
    protected $warehouseCode = 'О00013421';
    //protected $warehouseCode = 'О00013433'; //test

    public function addOrdersExport($date)
    {
        $sync = new ShopLogistic();
        $xml = $sync->getXml('add_zabor');
		
        $exports = $xml->addChild('zabors');
        $export = $exports->addChild('zabor');
        $export->addChild('zabor_places_code', $this->warehouseCode);
        $export->addChild('delivery_date', $date);
		
        $resp = $sync->getData($xml);
		return $sync->getError('add_delivery', $resp->zabors->zabor->error_code);
    }

    public function addDelivery($order)
    {
        $sync = new ShopLogistic();
        $xml = $sync->getXml('add_delivery');
        $deliveries = $xml->addChild('deliveries');

        $errors = [];

        $delivery = $deliveries->addChild('delivery');

        if($order->get('sl_id')) {
            $delivery->addChild('code', $order->get('sl_id'));
        }

        $attrs = $order->getPlugin('attrs');
        $phone = $order->getPlugin('phone')->get('phone');
        $city = $order->getCity();
		
		/*if(is_bool(\DateTime::createFromFormat('d.m.Y', $attrs->get('date')))) {
			echo $order->getId();
			var_dump($attrs->get('date'));die();
		}*/		
        
        $delivery->addChild('date_transfer_to_store', $attrs->get('export_date'));
        $delivery->addChild('from_city', '958281');
        $delivery->addChild('to_city', $city->get('code'));

        switch ($attrs->get('delivery')) {
            case Delivery::TYPE_COURIER:
                $delivery->addChild('time_from', $attrs->get('time_from'));
                $delivery->addChild('time_to', $attrs->get('time_to'));
                $delivery->addChild('address', $order->getDeliveryAddress());
                $delivery->addChild('address_index', $attrs->get('address_index'));
				$delivery->addChild('delivery_date', \DateTime::createFromFormat('d.m.Y', $attrs->get('date'))->format('Y-m-d'));
                break;
            case Delivery::TYPE_PICKUP:
                $point = $order->getPickupPoint();
                $delivery->addChild('pickup_place', $point->get('code'));
                $delivery->addChild('delivery_partner', $point->get('type'));
				$delivery->addChild('delivery_date', '2017-12-19');
                break;
            case Delivery::TYPE_POST:
                break;
            default:
                return 'Неизвестный тип доставки';
        }

        $delivery->addChild('order_id', $order->getId());
        $delivery->addChild('contact_person', $attrs->get('name'));

        $delivery->addChild('phone', $phone);
        $delivery->addChild('phone_sms', $phone);
        $delivery->addChild('price', ($order->isPaid() ? '0' : $order->getPrice()));
        $delivery->addChild('ocen_price', $order->getPrice());
        $delivery->addChild('additional_info', '');

        $settings = Settings::getInstance();
        $delivery->addChild('site_name', $settings->get('site_name'));
        $delivery->addChild('zabor_places_code', $this->warehouseCode);
        $delivery->addChild('partial_ransom', 0);
        $delivery->addChild('prohibition_opening_to_pay', 0);
        $delivery->addChild('delivery_price_for_customer', ($order->isPaid() ? '0' : $attrs->get('delivery_income')));
        $delivery->addChild('delivery_price_for_customer_required', 0);
        $delivery->addChild('delivery_price_porog_for_customer', $city->getFreeDeliveryPrice());
        $delivery->addChild('delivery_discount_for_customer', ($order->isPaid() ? $order->get('income') : '0'));
        $delivery->addChild('delivery_discount_porog_for_customer', 0);
        $delivery->addChild('return_shipping_documents', 0);
        $delivery->addChild('use_from_canceled', 0);
        $delivery->addChild('use_from_canceled', 0);
        $delivery->addChild('add_product_from_disct', 0);
        $delivery->addChild('number_of_place', 1);
        $delivery->addChild('delivery_speed', 'normal');
        $delivery->addChild('shop_logistics_cheque', 1);
        $delivery->addChild('return_from_reciver', 0);
        $delivery->addChild('barcodes', $sync->getBarcode($order->getId()));
        $delivery->addChild('customer_email', $order->getUser()->get('email'));
        $products = $delivery->addChild('products');

        foreach ($order->getPlugin('cart') as $cartRow) {
            $product = $cartRow->getPlugin('product');
            $productXml = $products->addChild('product');
            $productXml->addChild('articul', $cartRow->getId() . '-' . $cartRow->get('taste_id') . '-' . $cartRow->get('size_id'));
            $productXml->addChild('name', $product->get('name'));
            $productXml->addChild('quantity', $cartRow->get('count'));
            $productXml->addChild('item_price', $cartRow->get('price'));
            $productXml->addChild('item_barcode', '');
            $productXml->addChild('nds', '0');
        }

        $resp = $sync->getData($xml);
		
		//header('Content-Type: text/xml');
        //die($resp->asXML());

        $error = '';

        if($resp->deliveries->delivery->errors) {
            $error = nl2br($sync->getError('add_delivery', $resp->deliveries->delivery->errors));
        } else {
            $order->set('sl_id', $resp->deliveries->delivery->code);
        }

		return (string) $resp->deliveries->delivery->errors;
    }

    public function delDelivery($order)
    {
        return;

        /*if(!$order->get('sl_id')) {
            return;
        }*/
    }
}