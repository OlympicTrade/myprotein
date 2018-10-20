<?php

namespace Metrics\Service;

use Aptero\Seo\Platform;
use Metrics\Model\Adwords;
use Aptero\Service\AbstractService;
use Metrics\Model\Visit;
use Zend\Json\Json;
use Zend\Session\Container as SessionContainer;

class MetricsService extends AbstractService
{
    public function initMetrics()
    {
        $this->initVisitsMetrics();
    }

    public function initVisitsMetrics()
    {
        $visit = new Visit();

        $platform  = (new Platform())->getPlatform();
		$adwords   = $this->initAdwordsMetrics();
		$adwordsId = $adwords ? $adwords->getId() : 0;

        $visit->select()->where([
            'date'       => date('Y-m-01'),
            'platform'   => $platform,
            'adwords_id' => $adwordsId,
        ]);
        
        if(!$visit->load()) {
            $visit->setVariables([
                'platform'   => $platform,
				'adwords_id' => $adwordsId,
                'date'       => date('Y-m-01'),
            ]);
        }

        $visit->set('views', $visit->get('views') + 1);

        //$session = new SessionContainer('metrics');
        //$session->setExpirationSeconds(30 * 60);
		
        if(empty($_COOKIE['session'])) {
            $visit->set('sessions', $visit->get('session') + 1);
        }
		
		setcookie('session', 'init', time() + 60 * 20, '/');

        if(!isset($_COOKIE['metric'])) {
            $visit->set('clients', $visit->get('clients') + 1);
            $expire = time() + 60 * 60 * 24 * 360;
            setcookie('metric', 'init', $expire, '/');
        }
		
        $visit->save();
    }

    public function initAdwordsMetrics()
    {
		$cross = 0;
        if(!empty($_POST['query']['utm_source'])) {
            $campaign = $_POST['query']['utm_campaign'];
			$source  = $_POST['query']['utm_source'];
			
			$data = [
				'campaign'  => $_POST['query']['utm_campaign'],
				'source'   => $_POST['query']['utm_source'],
				'type'     => $_POST['query']['utm_medium'],
			];
			
			if(!empty($_COOKIE['utm'])) {
				try {
					$utmData = Json::decode($_COOKIE['utm']);
					
					if($utmData->campaign == $campaign && $utmData->source == $source) {
						$cross = 1;
					}
				} catch (\Exception $exception) {}
			}
			
			$expire = time() + 60 * 60 * 24 * 360;
			$_COOKIE['utm'] = Json::encode($data);
			setcookie('utm', $_COOKIE['utm'], $expire, '/');
        }

        $adwords = new Adwords();
		$adwords->loadFromCookie();
		
		if($cross) {
			$adwords->set('cross', $adwords->get('cross') + $cross)->save();
		}
		
        return $adwords;
    }
}