<?php
namespace MetricsAdmin\Service;

use Aptero\Service\Admin\TableService;
use Catalog\Model\Product;

class CashService extends TableService
{
    public function getPrice()
    {
        $products = (new Product())->addProperties([
            'size_id'   => [],
            'size'      => [],
            'taste_id'  => [],
            'taste'     => [],
        ])->getCollection();

        $filter = [
            'group' => 'ps.id',
            'yandexYmlFull' => true,
            'join'      => ['brands', 'brand-country', 'catalog'],
            'columns'   => ['id', 'catalog_id', 'type_id', 'brand_id', 'name', 'subname', 'discount', 'url', 'barcode']
        ];

        $select = $this->getProductsService()->getProductsSelect($filter);
        $select->where
            ->nest()
                ->greaterThan('ps.count', 0)
                ->and
                ->greaterThan('pst.coefficient', 0)
                ->and
                ->greaterThan('pss.price', 0)
            ->unnest();

        $products->setSelect($select);

        $price = 0;
        foreach($products as $product) {
            $price += $product->get('price_base') * $product->get('coefficient') * $product->get('stock');
        }

        return $price;
    }

    /**
     * @return \Catalog\Service\ProductsService
     */
    protected function getProductsService()
    {
        return $this->getServiceManager()->get('Catalog\Service\ProductsService');
    }
}