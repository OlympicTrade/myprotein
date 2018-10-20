<?php

namespace Catalog\Service;

use Application\Model\Settings;
use Aptero\Service\AbstractService;
use Catalog\Model\Catalog;
use Catalog\Model\Product;

class GoogleMerchant extends AbstractService
{
    public function getYml()
    {
        $settings = new Settings();

        $rootXML = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"></rss>');

        $gNS = 'http://base.google.com/ns/1.0';
        $rootXML->registerXPathNamespace('g', $gNS);

        $channelXML = $rootXML->addChild('channel');

        $channelXML->addChild('title', 'Products feed');
        $channelXML->addChild('link',  $settings->get('domain'));
        $channelXML->addChild('description', '');

        $this->setProducts($channelXML, $settings, $gNS);

        return $rootXML->asXML();
    }


    public function setDelivery($shopXML, $settings)
    {
        $deliveryXML = $shopXML->addChild('delivery-options');

        $optionXML = $deliveryXML->addChild('option');
        $optionXML->addAttribute('cost', '300');
        $optionXML->addAttribute('days', '1-3');

        return $shopXML;
    }

    public function setProducts($channelXML, $settings, $gNS)
    {
        $filter['join'] = ['brands'];
        $filter['columns'] = ['text'];

        $products = Product::getEntityCollection();

        $select = $this->getProductsService()->getProductsSelect($filter);

        $select->where(['go_merchant' => 1]);
        $select->join(array('c' => 'catalog'), 'c.id = t.catalog_id', [
            'catalog-id'            => 'id',
            'catalog-name'          => 'name',
            'catalog-go_market_id'  => 'go_market_id',
            'catalog-url_path'      => 'url_path',
        ], 'left');

        $products->setSelect($select);

        foreach($products as $product) {
            $productXml = $channelXML->addChild('item');

            $productXml->addChild('g:id', $product->getId(), $gNS);
            $productXml->addChild('g:title', htmlspecialchars($product->get('name')), $gNS);
            $productXml->addChild('g:link', $settings->get('domain') . '/goods/' . $product->get('url') . '/', $gNS);
            $productXml->addChild('g:mobile_link', $settings->get('mdomain') . '/goods/' . $product->get('url') . '/', $gNS);
            $productXml->addChild('g:image_link', $settings->get('domain') . $product->getPlugin('image')->getImage('hr'), $gNS);
            $productXml->addChild('g:condition', 'новый', $gNS);
            $productXml->addChild('g:availability', $product->get('stock') ? 'in stock' : 'preorder', $gNS);
            $productXml->addChild('g:price', $product->get('price_old'), $gNS);
            $productXml->addChild('g:brand', $product->getPlugin('brand')->get('name'), $gNS);

            if($product->get('price') != $product->get('price_old')) {
                $productXml->addChild('g:sale_price', $product->get('price'), $gNS);
            }

            $category = $product->getPlugin('catalog');

            if ($category->get('go_market_id')) {
                $productXml->addChild('g:google_product_category ', $category->get('go_market_id'), $gNS);
            }
        }

        return $channelXML;
    }

    /**
     * @return \Catalog\Service\ProductsService
     */
    protected function getProductsService()
    {
        return $this->getServiceManager()->get('Catalog\Service\ProductsService');
    }
}