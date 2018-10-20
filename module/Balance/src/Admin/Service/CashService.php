<?php
namespace BalanceAdmin\Service;

use Aptero\Service\Admin\TableService;
use BalanceAdmin\Model\BalanceFlow;
use Catalog\Model\Order;
use Catalog\Model\Product;
use CatalogAdmin\Model\Supplies;
use Zend\Db\Sql\Expression;
use BalanceAdmin\Model\Balance;

class CashService extends TableService
{
    /**
     * @param \Aptero\Db\Entity\EntityCollection $collection
     * @param $filters
     * @return \Aptero\Db\Entity\EntityCollection
     */
    public function setFilter($collection, $filters)
    {
        if($filters['search']) {
            $collection->select()->where->like('t.name', '%' . $filters['search'] . '%');
        }

        if($filters['type']) {
            $collection->select()->where(['type' => $filters['type']]);
        }

        if(!empty($filters['date_from'])) {
            $collection->select()->where->greaterThanOrEqualTo('date', $filters['date_from']);
        }

        if(!empty($filters['date_to'])) {
            $collection->select()->where->lessThanOrEqualTo('date', $filters['date_to']);
        }

        return $collection;
    }

    public function updateBalance()
    {
        $today = new \DateTime();

        $balance = new Balance();
        $balance->select()->where(array('date' => $today->format('Y-m-d')));
        $balance->load();

        $moneyCash = $this->getMoneyCash();

        $balance->setVariables(array(
            'date'  => $today->format('Y-m-d'),
            'products_cash'  => $this->getProductsCash() + $this->getSuppliesCash(),
            'orders_cash'    => $this->getOrdersCash(true),
            'money_cash'     => $moneyCash->cash + $moneyCash->orders,
            'orders_count'   => $this->getOrdersCount(),
        ));

        $balance->save();
    }

    public function getOrdersCount()
    {
        $today = new \DateTime();
        $dt = new \DateTime();
        $tomorrow  = $dt->modify('+1 day');

        $select = $this->getSql()->select()
            ->from(array('t' => 'orders'))
            ->columns(array('count' => new Expression('COUNT(*)')));

        $select->where
            ->greaterThanOrEqualTo('time_create', $today->format('Y-m-d'))
            ->lessThan('time_create', $tomorrow->format('Y-m-d'))
            ->nest()
            ->notEqualTo('status', Order::STATUS_CANCELED)
            ->unnest();

        $result = $this->execute($select)->current();

        return $result['count'];
    }

    public function getSuppliesCash()
    {
        $select = $this->getSql()->select()
            ->from(array('t' => 'supplies'))
            ->columns(array('cash' => new Expression('SUM(price * currency_rate)')))
            ->where(array('status' => array(
                Supplies::STATUS_NEW,
            )));

        $result = $this->execute($select)->current();

        return $result['cash'];
    }

    public function getOrdersCash($today = false)
    {
        $select = $this->getSql()->select()
            ->from(array('t' => 'orders'))
            ->columns(array('cash' => new Expression('SUM(profit)')))
            ->where(array('status' => array(
                //Order::STATUS_NEW,
                Order::STATUS_PROCESSING,
                Order::STATUS_DELIVERY
            )));

        if($today) {
            $today = new \DateTime();
            $dt = new \DateTime();
            $tomorrow  = $dt->modify('+1 day');

            $select->where
                ->greaterThanOrEqualTo('time_create', $today->format('Y-m-d'))
                ->lessThan('time_create', $tomorrow->format('Y-m-d'));
        }

        $result = $this->execute($select)->current();

        return $result['cash'];
    }

    public function getMoneyCash()
    {
        $result = array(
            'cash'   => 0,
            'frozen' => 0,
            'credit' => 0,
        );

        $select = $this->getSql()->select()
            ->from(array('t' => 'balance_flow'))
            ->columns(array('price', 'type'))
            ->where(array('status' => 0));

        foreach($this->execute($select) as $row) {
            if($row['type'] == BalanceFlow::TYPE_CREDIT) {
                $result['credit'] += $row['price'];
            } elseif($row['type'] == BalanceFlow::TYPE_SUPPLIES) {
                $result['orders'] += $row['price'];
            } else {
                $result['cash'] += $row['price'];
            }
        }

        return (object) $result;
    }

    public function getProductsCash()
    {
        $pSelect = $this->getSql()->select();
        $pSelect->from(['sp' => 'supplies_products'])
            ->columns(['price' => new Expression('AVG(sp.price * s.currency_rate)')])
            ->join(['s' => 'supplies'], 's.id = sp.supply_id', [])
            ->where([
                'sp.product_id' => new Expression('ps.product_id'),
                'sp.taste_id'   => new Expression('ps.taste_id'),
                'sp.size_id'    => new Expression('ps.size_id'),
            ])
            ->where
            ->greaterThan('sp.price', 0)
            ->greaterThan('s.date', (new \DateTime())->modify('-3 month')->format('Y-m-d'));

        $select = $this->getSql()->select();
        $select
            ->from(['ps' => 'products_stock'])
            ->columns(['count', 'price' => $pSelect])
            ->group('ps.product_id')->group('ps.size_id')->group('ps.taste_id');

        $result = $this->execute($select);

        $price = 0;
        foreach($result as $row) {
            $price += $row['count'] * $row['price'];
        }

        return $price;
    }
}