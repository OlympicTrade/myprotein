<?php
namespace MetricsAdmin\View\Helper;

use Aptero\String\Date;
use Zend\View\Helper\AbstractHelper;
use Zend\Form\ElementInterface;

class PeriodsMetrics extends AbstractHelper
{
    public function __invoke($metricsList, $type){
		switch($type) {
			case 'sales':   return $this->salesMetrics($metricsList);
			case 'adwords': return $this->adworsMetrics($metricsList);
			case 'visits':  return $this->visitsMetrics($metricsList);
		}
		
		throw new \Exception('Unknown metric type');
	}
	
	public function adworsMetrics($metricsList){
		$html = '';
        $view = $this->getView();

        foreach($metricsList as $metrics) {
            $adwords  = $metrics['adwords']['all'];
            $balance  = $metrics['balance']['all'];
			$visits   = $metrics['visits']['all'];
            $adwProfit = $adwords['income'] - $adwords['cost'];

            $header = Date::getMonths()[$metrics['date']['from']->format('n')];
            $url = '/admin/metrics/metrics/period/?'
                .'date_from=' . $metrics['date']['from']->format('Y-m-d')
                .'&date_to='  . $metrics['date']['to']->format('Y-m-d');

            $html .=
                '<div class="col-3">'
                    .'<div class="prod-stats">'
                        .'<h2 class="header">' . $header . ' <a href="' . $url . '">подробнее</a></h2>';
						
            $html .=
                $this->table('Реклама', [
                    'Посетители' => $view->price((int) $adwords['clients']),
                    'Просмотры'  => $view->price((int) $adwords['views']),
                    'Перекрытие' => $view->price((int) $adwords['cross']),
                    'Заказы'   => $view->price((int) $adwords['orders']),
                    'Доход'    => $view->price((int) $adwords['income']) . ' руб.',
                    'Расход'   => $view->price((int) $adwords['cost']) . ' руб.',
                    'Чистая прибыль'  => '(' . (int) @($adwProfit / $adwords['cost']  * 100) . '%) '
                        . $view->price($adwProfit) . ' руб.',
                ])
				.
				$this->table('Доля от всех заказов', [
                    'Посетители'  	 => @$view->price((int) ($adwords['clients'] / $visits['clients']  * 100)) . '%',
                    'Просмотры'  	 => @$view->price((int) ($adwords['views'] / $visits['views']  * 100)) . '%',
                    'Заказы'  		 => @$view->price((int) ($adwords['orders'] / $balance['count']  * 100)) . '%',
                    'Доход' 		 => @$view->price((int) ($adwords['income'] / $balance['income']  * 100)) . '%',
                    'Чистая прибыль' => @$view->price((int) ($adwProfit / ($balance['profit'] - $adwords['cost'])  * 100)) . '%',
                ])
				.
				$this->table('Стоимость одного:', [
                    'Посетителя'  	 => @$view->price((int) ($adwProfit / $adwords['clients'])),
                    'Просмотра'  	 => @$view->price((int) ($adwProfit / $adwords['views'])),
                    'Заказа'  		 => @$view->price((int) ($adwProfit / $adwords['orders'])),
                ]);
            
            $html .=
                    '</div>'
                .'</div>';
        }
		
		$html .= '<div class="clear"></div>';
		
        return $html;
	}
	
    public function visitsMetrics($metricsList){
		$html = '';
        $view = $this->getView();

        foreach($metricsList as $metrics) {
            $visits  = $metrics['visits'];
			$date = $metrics['date'];

            $header = Date::getMonths()[$date['from']->format('n')];
            $url = '/admin/metrics/metrics/period/?'
                .'date_from=' . $date['from']->format('Y-m-d')
                .'&date_to='  . $date['to']->format('Y-m-d');


            $html .=
                '<div class="col-3">'
                    .'<div class="prod-stats">'
                        .'<h2 class="header">' . $header . ' <a href="' . $url . '">подробнее</a></h2>';

			$va = $visits['all'];
			$vm = $visits['mobile'];
			$vd = $visits['desktop'];
			
            $html .=
                $this->table('Посещения', [
                    'Просмотры'   => $view->price($va['views']),
                    'Сессии'      => $view->price($va['sessions']),
                    'Посетители'  => $view->price($va['clients']),
                ]);
			$html .=
                $this->table('Источники', [
                    'Просмотры' 		 => 
						 '<i class="fas fa-tv"></i> ' 	 . @str_pad((int) ($vd['views'] / $va['views'] * 100), 2, '0', 0) . '% | '
						.'<i class="fas fa-mobile"></i> ' . @str_pad((int) ($vm['views'] / $va['views'] * 100), 2, '0', 0) . '%',
                    'Сессии'   		 => 
						 '<i class="fas fa-tv"></i> ' 	 . @str_pad((int) ($vd['sessions'] / $va['sessions'] * 100), 2, '0', 0) . '% | '
						.'<i class="fas fa-mobile"></i> ' . @str_pad((int) ($vm['sessions'] / $va['sessions'] * 100), 2, '0', 0) . '%',
                    'Посетители'      => 
						 '<i class="fas fa-tv"></i> ' 	 . @str_pad((int) ($vd['clients'] / $va['clients'] * 100), 2, '0', 0) . '% | '
						.'<i class="fas fa-mobile"></i> ' . @str_pad((int) ($vm['clients'] / $va['clients'] * 100), 2, '0', 0) . '%',
                ]);
            
            $html .=
                    '</div>'
                .'</div>';
        }
		
		$html .= '<div class="clear"></div>';
		
        return $html;
	}
	
    public function salesMetrics($metricsList){
        $html = '';
        $view = $this->getView();

        foreach($metricsList as $metrics) {
            $balance = $metrics['balance'];
            $adwords = $metrics['adwords'];
            //$visits  = $metrics['visits'];
            $adwProfit = $adwords['income'] - $adwords['cost'];

            $header = Date::getMonths()[$metrics['date']['from']->format('n')];
            $url = '/admin/metrics/metrics/period/?'
                .'date_from=' . $metrics['date']['from']->format('Y-m-d')
                .'&date_to='  . $metrics['date']['to']->format('Y-m-d');


            $html .=
                '<div class="col-3">'
                    .'<div class="prod-stats">'
                        .'<h2 class="header">' . $header . ' <a href="' . $url . '">подробнее</a></h2>';

			$ba = $balance['all'];
			$bm = $balance['mobile'];
			$bd = $balance['desktop'];
						
            $html .=
                $this->table('Продажи', [
                    'Заказов' 		 => $view->price($ba['count']),
                    'Доход'   		 => $view->price($ba['income']) . ' руб.',
                    'Расход'   		 => $view->price($ba['outgo'] + $adwords['cost']) . ' руб.',
                    'Сред. чек'      => $view->price(@ (int)($ba['income'] / $ba['count'])) . ' руб.',
                    'Сред. наценка'  => (int) @($ba['profit'] / $ba['income'] * 100) . '%',
                    'Чистая прибыль' => $view->price($ba['profit'] - $adwords['cost']) . ' руб.'
                ])
				.
                $this->table('Источники', [
					'Заказов' 		 => 
						 '<i class="fas fa-tv"></i> ' 	 . @str_pad((int) ($bd['count'] / $ba['count'] * 100), 2, '0', 0) . '% | '
						.'<i class="fas fa-mobile"></i> ' . @str_pad((int) ($bm['count'] / $ba['count'] * 100), 2, '0', 0) . '%',
					'Доход' 		 => 
						 '<i class="fas fa-tv"></i> ' 	 . @str_pad((int) ($bd['income'] / $ba['income'] * 100), 2, '0', 0) . '% | '
						.'<i class="fas fa-mobile"></i> ' . @str_pad((int) ($bm['income'] / $ba['income'] * 100), 2, '0', 0) . '%',
					'Сред. чек' 		 => 
						 '<i class="fas fa-tv"></i> ' 	 . @str_pad((int) (($bd['income'] / $bd['count']) / ($ba['income'] / $ba['count']) * 100), 2, '0', 0) . '% | '
						.'<i class="fas fa-mobile"></i> ' . @str_pad((int) (($bm['income'] / $bm['count']) / ($ba['income'] / $ba['count']) * 100), 2, '0', 0) . '%',
					'Чистая прибыль' 		 => 
						 '<i class="fas fa-tv"></i> ' 	 . @str_pad((int) (($bd['profit'] - $bd['outgo']) / ($ba['profit'] - $ba['outgo']) * 100), 2, '0', 0) . '% | '
						.'<i class="fas fa-mobile"></i> ' . @str_pad((int) (($bm['profit'] - $bm['outgo']) / ($ba['profit'] - $ba['outgo']) * 100), 2, '0', 0) . '%',
				]);
            
            
            $html .=
                    '</div>'
                .'</div>';
        }
		
		$html .= '<div class="clear"></div>';
		
        return $html;
    }

    protected function table($title, $data)
    {
        $html =
            '<table class="std-table">'
                .'<tbody>'
                .'<tr>'
                    .'<th colspan="2">' . $title . '</th>'
                .'</tr>';

        foreach ($data as $key => $val) {
            $html .=
                '<tr>'
                    .'<td>' . $key . '</td>'
                    .'<td class="ta-r">' . $val . '</td>'
                .'</tr>';
        }

        $html .=
                '</tbody>'
            .'</table>';

        return $html;
    }
}