<?php
namespace Metrics\Model;

use Aptero\Db\Entity\Entity;
use Zend\Json\Json;

class Adwords extends Entity
{
    static public $types = [
        'cpc'      => 'ppc',
        'email'    => 'E-mail',
        'banner'   => 'Баннер',
        'organic'  => 'Поиск',
        'referral' => 'Ссылка',
        'sm'       => 'Соц. сети',
    ];
    
    public function __construct()
    {
        $this->setTable('metrics_adwords');
        
        $this->addProperties([
            'source'    => [],
            'campaign'  => [],
            'src_type'  => [],
            'cross'     => [],
            'date'      => ['default' => date('Y-m-d')],
        ]);
    }
    
    public function loadFromCookie()
    {
        if(empty($_COOKIE['utm'])) {
            return false;
        }
		
		try {
            $utmData = Json::decode($_COOKIE['utm']);
        } catch (\Exception $exception) {
            return false;
        }

        $this->select()->where([
            'source'   => $utmData->source,
            'date'     => date('Y-m-01'),
        ]);

        if($utmData->campaign !== null) {
            $this->select()->where(['campaign' => $utmData->campaign]);
        }

		if(!$this->load()) {
            $this->setVariables([
				'campaign' => ucfirst($utmData->campaign),
				'source'   => ucfirst($utmData->source),
				'date'     => date('Y-m-01'),
            ]);

            if(!empty($utmData->type) && array_key_exists($utmData->type, self::$types)) {
                $this->set('src_type', $utmData->type);
            }

            $this->save();
        }

        return $this->load();
    }
}