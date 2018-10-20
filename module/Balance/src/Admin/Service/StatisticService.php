<?php
namespace BalanceAdmin\Service;

use Aptero\Service\Admin\TableService;
use CatalogAdmin\Model\Orders;
use Zend\Db\Sql\Predicate\Expression;

class StatisticService extends TableService
{
    public function getStatistic($filters)
    {
        return $this->getCashStatistic($filters);
        
        /*$select = $this->getSql()->select();

        $select
            ->from(array('t' => 'balance'));

        if(!empty($filters['date_from'])) {
            $select->where->greaterThanOrEqualTo('t.date', $filters['date_from']);
        }

        if(!empty($filters['date_to'])) {
            $select->where->lessThanOrEqualTo('t.date', $filters['date_to']);
        }
        
        $labels = array(
            'field' => 'date',
            'units' => ''
        );

        $graphs = array();

        switch($filters['type']) {
            case 'orders_avg':
                $graphs[] = $this->getCashStatistic('orders_avg', $filters);
                break;
            case 'orders_sum':
                $graphs[] = $this->getCashStatistic('orders_sum', $filters);
                break;
            case 'check_avg':
                $graphs[] = $this->getCashStatistic('check_avg', $filters);
                $labels['units'] = 'руб.';
                break;
            case 'income_sum':
                $graphs[] = $this->getCashStatistic('income_sum', $filters);
                $labels['units'] = 'руб.';
                break;
            case 'assets_sum':
                $graphs[] = $this->getCashStatistic('assets', $filters);
                $labels['units'] = 'руб.';
                break;
            case 'assets':
                $graphs = array(
                    $this->getCashStatistic('products_avg', $filters),
                    $this->getCashStatistic('income_avg', $filters),
                    $this->getCashStatistic('money_avg', $filters)
                );

                $labels['units'] = 'руб.';
                break;
        }

        $fields = array(
            'labels' => $labels,
            'graph'  => $graphs
        );

        return array(
            'data'   => $this->execute($select),
            'fields' => $fields,
        );*/
    }

    public function getSaleStatistic($filters)
    {
        $select =
            $this->getSql()->select()
                ->from(['o' => 'orders'])
                ->columns([/*'o_count' => new Expression('count(DISTINCT o.id)'),*/ ])
                ->join(['c' => 'orders_cart'], 'c.order_id = o.id', [])
                ->join(['p' => 'products'], 'c.product_id = p.id', ['id', 'name', 'p_count' => new Expression('COUNT(p.id)')])
                ->group('p.id')
                ->order('p_count DESC')
                ->limit(10);
      
        if(!empty($filters['date_from'])) {
            $select->where->greaterThanOrEqualTo('o.time_create', $filters['date_from']);
        }

        if(!empty($filters['date_to'])) {
            $select->where->lessThanOrEqualTo('o.time_create', $filters['date_to']);
        }

        return $this->execute($select);
    }
    
    public function getCashStatistic($filters)
    {
        $select = $this->getSql()->select();

        $select
            ->from(['o' => 'orders'])
            ->columns(['id', 'time_create', 'income' => new Expression('SUM(c.price * c.count)')])
            ->join(['c' => 'orders_cart'], 'c.order_id = o.id', ['profit' => new Expression('SUM(c.price * c.count) - SUM(c.price * c.count * (1 - (p.margin / 100)))')])
            ->join(['p' => 'products'], 'c.product_id = p.id', ['margin'])
            ->where
                ->in('o.status', [Orders::STATUS_PROCESSING, Orders::STATUS_COMPLETE, Orders::STATUS_NEW, Orders::STATUS_DELIVERY]);

        if(!empty($filters['date_from'])) {
            $select->where->greaterThanOrEqualTo('o.time_create', $filters['date_from']);
        }

        if(!empty($filters['date_to'])) {
            $select->where->lessThanOrEqualTo('o.time_create', $filters['date_to']);
        }

        return $this->execute($select)->current();

        /*$select = $this->getSql()->select();

        $select
            ->from(array('t' => 'balance'));

        if(!empty($filters['date_from'])) {
            $select->where->greaterThanOrEqualTo('t.date', $filters['date_from']);
        }

        if(!empty($filters['date_to'])) {
            $select->where->lessThanOrEqualTo('t.date', $filters['date_to']);
        }

        $columns = array();
        $graph = array(
            'name'   => '',
            'data'   => null,
            'filter' => function($row) {
                return round((int) $row['data']);
            },
        );

        switch($type) {
            case 'orders_avg':
                $columns['data'] = new Expression('AVG(orders_count)');
                $graph['name']   = 'Среднее кол-во заказов';
                break;
            case 'orders_sum':
                $columns['data'] = new Expression('SUM(orders_count)');
                $graph['name']   = 'Всего заказов';
                break;
            case 'check_avg':
                $columns['data'] = new Expression('AVG(orders_cash / orders_count)');
                $graph['name']   = 'Средний чек';
                break;
            case 'income_sum':
                $columns['data'] = new Expression('SUM(orders_cash)');
                $graph['name']   = 'Суммарный доход';
                break;
            case 'income_avg':
                $columns['data'] = new Expression('AVG(orders_cash)');
                $graph['name']   = 'Заказы';
                break;
            case 'products_avg':
                $columns['data'] = new Expression('AVG(products_cash)');
                $graph['name']   = 'Товары';
                break;
            case 'money_avg':
                $columns['data'] = new Expression('AVG(money_cash)');
                $graph['name']   = 'Наличные';
                break;
            case 'assets':
                $columns['data'] = new Expression('MAX(orders_cash + products_cash + money_cash)');
                $graph['name']   = 'Активы';
                break;
        }

        switch($filters['interval']) {
            case 'year':
                $columns['date'] = new Expression("CONCAT('01.01.', YEAR(t.date))");
                $select->group(new Expression('YEAR(t.date)'));
                break;
            case 'month':
                $columns['date'] = new Expression("CONCAT('01.', MONTH(t.date), '.', YEAR(t.date))");
                $select->group(new Expression('YEAR(t.date), MONTH(t.date)'));
                break;
            case 'day':
                $columns['date'] = new Expression("CONCAT(DAY(t.date), '.', MONTH(t.date), '.', YEAR(t.date))");
                $select->group(new Expression('YEAR(t.date), MONTH(t.date), DAY(t.date)'));
                break;
            default:
                $columns['date'] = new Expression("CONCAT(DAY(t.date), '.', MONTH(t.date), '.', YEAR(t.date))");
                $select->group(new Expression('YEAR(t.date), WEEK(t.date)'));
                break;
        }

        $select->columns($columns)
            ->order('t.date');

        $graph['data'] = $this->execute($select);

        return $graph;*/
    }
}