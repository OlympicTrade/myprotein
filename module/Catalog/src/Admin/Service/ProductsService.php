<?php
namespace CatalogAdmin\Service;

use Aptero\Service\Admin\TableService;
use CatalogAdmin\Model\Cart;
use CatalogAdmin\Model\Orders;
use Zend\Db\Sql\Expression;

class ProductsService extends TableService
{
    const TABLE_STOCK       = 'products_stock';
    const TABLE_STOCK_LIMIT = 'products_stock_limit';

    public function getListBaseSelect()
    {
        $list = $this->getModel()->getCollection();
        $list->select()->order('sort DESC');

        return $list;
    }

    public function getStatistic($productId, $filters = [])
    {
        $select =
            $this->getSql()->select()
                ->from(['c' => 'orders_cart'])
                ->columns(['id', 'order_id', 'product_id', 'size_id', 'taste_id', 'count' => new Expression('SUM(c.count)')])
                ->join(['o' => 'orders'], 'c.order_id = o.id', [])
                ->join(['pp' => 'products_size'], 'c.size_id = pp.id', [])
                ->join(['pt' => 'products_taste'], 'c.taste_id = pt.id', [])
                ->group('c.product_id')
                ->order('count DESC');

        $select->where
            ->equalTo('c.product_id', $productId)
            ->notEqualTo('c.size_id', 0)
            ->notEqualTo('c.taste_id', 0)
            ->notIn('o.status', [Orders::STATUS_CANCELED]);

        if($filters['period']) {
            $dt = new \DateTime();

            switch ($filters['period']) {
                case 'half';
                    $dt->modify('-6 months');
                    break;
                case 'month';
                    $dt->modify('-2 months');
                    break;
            }

            $select->where->greaterThanOrEqualTo('o.time_create', $dt->format('Y:m:d H:i:s'));
        }
        
        if($filters['group']) {
            switch ($filters['group']) {
                case 'size':
                    $select->group('c.size_id');
                    break;
                case 'taste':
                    $select->group('c.taste_id');
                    break;
            }
        } else {
            $select->group('c.size_id')->group('c.taste_id');
        }

        $cart = Cart::getEntityCollection();
        $cart->setSelect($select);

        return $cart;
    }

    public function getStock($productId)
    {
        $select = $this->getSql()->select(self::TABLE_STOCK);
        $select->where(['product_id' => $productId]);

        return $this->execute($select);
    }

    public function getStockLimit($productId)
    {
        $select = $this->getSql()->select(self::TABLE_STOCK_LIMIT);
        $select->where(['product_id' => $productId]);

        return $this->execute($select);
    }
    
    
    public function updateStock($data)
    {
        $where = [
            'product_id' => $data['product_id'],
            'size_id'    => $data['size_id'],
            'taste_id'   => $data['taste_id'],
        ];

        $select = $this->getSql()->select(self::TABLE_STOCK);
        $select->where($where);
        $cStock = $this->execute($this->getSql()
            ->select(self::TABLE_STOCK)
            ->columns(['count'])
            ->where($where)
        );

        $count = 0;
        if($cStock) {
            $count = $cStock->current()['count'];
        }

        $diff = $data['count'] - $count;

        $this->getPublicOrderService()->changeProductCount($data['product_id'], $data['taste_id'], $data['size_id'], $diff);
    }

    public function updateStockLimit($data)
    {
        $where = [
            'product_id' => $data['product_id'],
            'size_id'    => $data['size_id'],
            'taste_id'   => $data['taste_id'],
        ];

        $select = $this->getSql()->select(self::TABLE_STOCK_LIMIT);
        $select->where($where);

        if(count($this->execute($select))) {
            $update = $this->getSql()->update(self::TABLE_STOCK_LIMIT);
            $update->where($where)
                ->set($data);
            $this->execute($update);
        } else {
            $insert = $this->getSql()->insert(self::TABLE_STOCK_LIMIT);
            $insert->values($data);
            $this->execute($insert);
        }
    }

    /**
     * @return \Catalog\Service\OrdersService
     */
    protected function getPublicOrderService()
    {
        return $this->getServiceManager()->get('Catalog\Service\OrdersService');
    }
}