<?php

namespace Sync\Service;

use Aptero\Service\AbstractService;
use CatalogAdmin\Model\Products;
use Sync\Model\SyncStock;
use Zend\Db\Sql\Expression;
use Zend\Json\Json;

class SyncService extends AbstractService
{
    const SITE = 'http://olympic-torch.ru';

    public function eraseChanges()
    {
        $this->execute($this->getSql()->delete('sync_stock_diff'));
        
        return ['status' => 1];
    }

    public function syncChanges()
    {
        $resp = ['status' => 0];

        $data = file_get_contents(self::SITE . '/sync/stock/data/');

        try {
            $data = Json::decode($data);
        } catch (\Exception $e) {
            $resp['error'] = 'Cant parse changes data';
            return $resp;
        }

        foreach ($data as $row) {
            $diff = (int) $row->diff;

            $select = $this->getSql()->select('products_stock');
            $select->columns(['id', 'count'])
                ->where([
                    'product_id' => $row->product_id,
                    'size_id'    => $row->size_id,
                    'taste_id'   => $row->taste_id,
                ]);
            $result = $this->execute($select);

            if(!$result) { continue; }

            $cData = $result->current();

            $count = $cData['count'] + $diff;
            $count = max(0, $count);

            $update = $this->getSql()->update('products_stock')
                ->where(['id' => $cData['id']])
                ->set(['count' => $count]);

            $this->execute($update);
        }

        file_get_contents(self::SITE . '/sync/stock/erase/');
        $resp['status'] = 1;

        file_get_contents(self::SITE . '/sync/stock/changes/');

        return $resp;
    }
    
    public function getChanges()
    {
        $sync = SyncStock::getEntityCollection();

        $sync->select()
            ->columns(['diff', 'product_id', 'size_id', 'taste_id']);

        $data = [];
        foreach ($sync as $row) {
            $data[] = [
                'product_id'    => $row->get('product_id'),
                'size_id'       => $row->get('size_id'),
                'taste_id'      => $row->get('taste_id'),
                'diff'          => $row->get('diff'),
            ];
        }

        return $data;
    }

    public function getProductData($productId)
    {
        $data = [
            'size'  => [],
            'price' => [],
            'stock' => [],
        ];

        $product = new Products();
        $product->setId($productId);

        if(!$product->load()) {
            return false;
        }

        foreach ($product->getPlugin('size') as $taste) {
            $data['size'][] = [
                'id'     => $taste->getId(),
                'name'   => $taste->get('name'),
                'price'  => $taste->get('price'),
                'weight' => $taste->get('weight'),
            ];
        }

        foreach ($product->getPlugin('taste') as $taste) {
            $data['price'][] = [
                'id'          => $taste->getId(),
                'name'        => $taste->get('name'),
                'coefficient' => $taste->get('coefficient'),
            ];
        }

        $select = $this->getSql()->select()
            ->from(['t' => 'products_stock'])
            ->columns(['count', 'size_id', 'taste_id'])
            ->join(['s' => 'products_size'],  't.size_id = s.id', ['size' => 'name'])
            ->join(['p' => 'products_taste'], 't.taste_id = p.id', ['price' => 'name'])
            ->where(['product_id' => $product->getId()]);

        foreach ($this->execute($select) as $row) {
            $data['stock'][] = $row;
        }

        return $data;
    }

    public function addProductToSync($options)
    {
        $where = [
            'product_id'    => $options['product_id'],
            'size_id'       => $options['size_id'],
            'taste_id'      => $options['taste_id'],
        ];

        $sync = new SyncStock();
        $sync->select()->where($where);

        if(!$sync->load()) {
            $sync->setVariables($where);
        }

        $sync->load();
        $sync->set('diff', $sync->get('diff') + $options['diff'])
            ->save();
    }
}