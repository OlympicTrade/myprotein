<?php
namespace MetricsAdmin\Service;

use Aptero\Service\Admin\TableService;
use Aptero\String\Date;
use CatalogAdmin\Model\Orders;
use Delivery\Model\City;
use Zend\Db\Sql\Expression;

class MetricsService extends TableService
{
    public function getVisitsStatistic($filters)
    {
        $select = $this->getSql()->select();
        $select
            ->from(['mv' => 'metrics_visits'])
            ->columns([
                'clients'  => new Expression('SUM(clients)'),
                'sessions' => new Expression('SUM(sessions)'),
                'views'    => new Expression('SUM(views)'),
            ]);

        if(!empty($filters['platform'])) {
            $select->where(['platform' => $filters['platform']]);
        }

        if(!empty($filters['date_from'])) {
            $select->where->greaterThanOrEqualTo('mv.date', $filters['date_from']);
        }

        if(!empty($filters['date_to'])) {
            $select->where->lessThanOrEqualTo('mv.date', $filters['date_to']);
        }

        return $this->execute($select)->current();
    }

    public function getMetrics()
    {
        $from = \DateTime::createFromFormat('Y-m-d', date('Y-m-01'))->modify('-3 month');
        $to = clone $from;
        $to->modify('+' . ($from->format('t') - 1) . ' days');

        $results = [];

        for($i = 1; $i <= 4; $i++) {
            $filters = [
                'date_from' => $from->format('Y-m-d'),
                'date_to'   => $to->format('Y-m-d'),
            ];

            $results[] = [
                'date'    => [
                    'from' 	  => clone $from,
                    'to'      => clone $to,
                ],
                'balance' => [
					'all'     => $this->getBalanceStatistic($filters),
					'desktop' => $this->getBalanceStatistic($filters + ['platform' => 'desktop']),
					'mobile'  => $this->getBalanceStatistic($filters + ['platform' => 'mobile']),
				],
                'adwords' => [
					'all' => $this->getAdwordsStatistic($filters + ['group' => false])->current(),
					//'groupe'  => $this->getAdwordsStatistic($filters + ['group' => true]),
				],
                'visits'  => [
                    'all'     => $this->getVisitsStatistic($filters),
                    'desktop' => $this->getVisitsStatistic($filters + ['platform' => 'desktop']),
                    'mobile'  => $this->getVisitsStatistic($filters + ['platform' => 'mobile']),
                ],
            ];

            $from->modify('+1 month');
            $to->modify('+' . ($from->format('t')) . ' days');
        }

        return $results;
    }

    public function getAdwordsStatistic($filters)
    {
        $filters = array_merge([
            'group' => false
        ], $filters);

        $oSelect = $this->getSql()->select();
        $oSelect->from(['o' => 'orders'])
            ->columns(['income' => new Expression('SUM(income + delivery_income - outgo - delivery_outgo)')]);
			
		$ocSelect = $this->getSql()->select();
        $ocSelect->from(['o' => 'orders'])
            ->columns(['count' => new Expression('COUNT(*)')]);

        $vSelect = $this->getSql()->select()
			->from(['mv' => 'metrics_visits'])
			->columns(['views' => new Expression('SUM(views)')]);
		
        $cSelect = clone $vSelect;
        $cSelect->columns(['clients' => new Expression('SUM(clients)')]);

        $select = $this->getSql()->select();
        $select
            ->from(['ma' => 'metrics_adwords']);

        if($filters['group']) {
            $oSelect->where(['o.adwords_id' => new Expression('ma.id')]);
			$ocSelect->where(['o.adwords_id' => new Expression('ma.id')]);
			$vSelect->where(['mv.adwords_id' => new Expression('ma.id')]);
			$cSelect->where(['mv.adwords_id' => new Expression('ma.id')]);

			//die($this->getSql()->buildSqlString($oSelect));
            $select
                ->columns([
					'id',
					'source',
					'campaign',
                    'cost'    => new Expression('SUM(cost)'),
                    'cross'   => new Expression('SUM(`cross`)'),
                    //'income'  => new Expression('SUM(' . $oSelect . ')'),
                    'income'  => $oSelect,
                    'orders'  => $ocSelect,
                    'views'   => $vSelect,
                    'clients' => $cSelect,
				])
                ->group('ma.source')/*->group('ma.campaign')*/;
        } else {
			$ocSelect->where->notEqualTo('o.adwords_id', 0);
            $oSelect->where->notEqualTo('o.adwords_id', 0);
			$vSelect->where->notEqualTo('mv.adwords_id', 0);
			$cSelect->where->notEqualTo('mv.adwords_id', 0);

            $select
                ->columns([
                    'id',
                    'cost'    => new Expression('SUM(cost)'),
                    'cross'   => new Expression('SUM(`cross`)'),
                    'income'  => $oSelect,
                    'orders'  => $ocSelect,
                    'views'   => $vSelect,
                    'clients' => $cSelect,
				]);
        }

        if(!empty($filters['date_from'])) {
            $select->where->greaterThanOrEqualTo('ma.date', $filters['date_from']);
            $oSelect->where->greaterThanOrEqualTo('o.time_create', $filters['date_from']);
            $ocSelect->where->greaterThanOrEqualTo('o.time_create', $filters['date_from']);
            $vSelect->where->greaterThanOrEqualTo('mv.date', $filters['date_from']);
            $cSelect->where->greaterThanOrEqualTo('mv.date', $filters['date_from']);
        }

        if(!empty($filters['date_to'])) {
            $select->where->lessThanOrEqualTo('ma.date', $filters['date_to']);
            $oSelect->where->lessThanOrEqualTo('o.time_create', $filters['date_to']);
            $ocSelect->where->lessThanOrEqualTo('o.time_create', $filters['date_to']);
            $vSelect->where->lessThanOrEqualTo('mv.date', $filters['date_to']);
            $cSelect->where->lessThanOrEqualTo('mv.date', $filters['date_to']);
        }

        switch ($filters['region']) {
            case 'msk':
                $oSelect->where(['city_id' => City::$mscId]);
                $ocSelect->where(['city_id' => City::$mscId]);
                break;
            case 'spb':
                $oSelect->where(['city_id' => City::$spbId]);
                $ocSelect->where(['city_id' => City::$spbId]);
                break;
            case 'regions':
                $oSelect->where->notIn('city_id', [City::$spbId, City::$mscId]);
                $ocSelect->where->notIn('city_id', [City::$spbId, City::$mscId]);
                break;
            default:
                break;

        }
		
        return $this->execute($select);
    }
	
    public function getSaleStatistic($filters)
    {
        $dateFrom = \DateTime::createFromFormat('Y-m-d', $filters['date_from']);

        $bSelect = $this->getSql()->select();
        $bSelect->from(['sp' => 'supplies_products'])
            ->columns(['b_price' => new Expression('AVG(sp.price * s.currency_rate)')])
            ->join(['s' => 'supplies'], 's.id = sp.supply_id', [])
            ->where([
                'sp.product_id' => new Expression('c.product_id'),
                'sp.taste_id'   => new Expression('c.taste_id'),
                'sp.size_id'    => new Expression('c.size_id'),
            ])
            ->where
            ->greaterThan('sp.price', 0)
            ->greaterThan('s.date', $dateFrom->modify('-2 month')->format('Y-m-d'));

        $select = $this->getSql()->select();
        $select
            ->from(['o' => 'orders'])
            //->columns(['b_price' => $bSelect])
            ->join(['c' => 'orders_cart'], 'c.order_id = o.id', ['s_price' => 'price', 'count' => new Expression('COUNT(*)')])
            ->join(['p' => 'products'], 'c.product_id = p.id', ['name'])
            ->join(['pt' => 'products_taste'], 'c.taste_id = pt.id', ['taste' => 'name'], 'left')
            ->join(['ps' => 'products_size'], 'c.size_id = ps.id', ['size' => 'name'], 'left')
            ->group('c.product_id')//->group('c.size_id')->group('c.taste_id')
            //->order('b_price * count ASC')
            ->order(new Expression('s_price * c.count DESC'))
            ->limit(10)
            ->where
                ->in('o.status', [Orders::STATUS_PROCESSING, Orders::STATUS_COLLECTED, Orders::STATUS_COMPLETE, Orders::STATUS_DELIVERY]);

        if(!empty($filters['date_from'])) {
            $select->where->greaterThanOrEqualTo('o.time_create', $filters['date_from']);
        }

        if(!empty($filters['date_to'])) {
            $select->where->lessThanOrEqualTo('o.time_create', $filters['date_to']);
        }

        return $this->execute($select);
    }
    
    public function getBalanceStatistic($filters)
    {
        $select = $this->getSql()->select();
        $select
            ->from(['o' => 'orders'])
            ->columns([
				'outgo'  => new Expression('SUM(outgo + delivery_outgo)'),
				'income' => new Expression('SUM(income + delivery_income)'),
				'profit' => new Expression('SUM(income + delivery_income - outgo - delivery_outgo)'),
				'count'  => new Expression('COUNT(*)')
			])
            ->where
            ->in('o.status', [Orders::STATUS_PROCESSING, Orders::STATUS_COLLECTED, Orders::STATUS_COMPLETE, Orders::STATUS_DELIVERY]);

        if(!empty($filters['date_from'])) {
            $select->where->greaterThanOrEqualTo('o.time_create', $filters['date_from']);
        }

        if(!empty($filters['date_to'])) {
            $select->where->lessThanOrEqualTo('o.time_create', $filters['date_to']);
        }

        if(!empty($filters['platform'])) {
            $select
				->join(['oa' => 'orders_attributes'], new Expression('oa.depend = o.id AND oa.key = "platform"'), [])
				->where(['oa.value' => $filters['platform']]);
        }

        return $this->execute($select)->current();
        /*$dateFrom = \DateTime::createFromFormat('Y-m-d', $filters['date_from']);

        $bSelect = $this->getSql()->select();
        $bSelect->from(['sp' => 'supplies_products'])
            ->columns(['b_price' => new Expression('AVG(sp.price * s.currency_rate)')])
            ->join(['s' => 'supplies'], 's.id = sp.supply_id', [])
            ->where([
                'sp.product_id' => new Expression('c.product_id'),
                'sp.taste_id'   => new Expression('c.taste_id'),
                'sp.size_id'    => new Expression('c.size_id'),
            ])
            ->where
                ->greaterThan('sp.price', 0)
                ->greaterThan('s.date', $dateFrom->modify('-2 month')->format('Y-m-d'));

        $select = $this->getSql()->select();
        $select
            ->from(['o' => 'orders'])
            ->columns(['b_price' => $bSelect, 'count' => new Expression('COUNT(*)')])
            ->join(['c' => 'orders_cart'], 'c.order_id = o.id', ['s_price' => 'price'])
            ->join(['p' => 'products'], 'c.product_id = p.id', ['name'])
            ->group('c.product_id')->group('c.size_id')->group('c.taste_id')
            ->order('b_price * count ASC')
            ->where
                ->in('o.status', [Orders::STATUS_PROCESSING, Orders::STATUS_COMPLETE, Orders::STATUS_DELIVERY]);

        if(!empty($filters['date_from'])) {
            $select->where->greaterThanOrEqualTo('o.time_create', $filters['date_from']);
        }

        if(!empty($filters['date_to'])) {
            $select->where->lessThanOrEqualTo('o.time_create', $filters['date_to']);
        }

        return $this->execute($select);*/
    }

    public function getOldSaleStatistic($filters)
    {
        $select =
            $this->getSql()->select()
                ->from(['o' => 'orders'])
                ->columns([])
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

    public function getOldCashStatistic($filters)
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