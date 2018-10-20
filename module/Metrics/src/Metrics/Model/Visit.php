<?php
namespace Metrics\Model;

use Aptero\Db\Entity\Entity;

class Visit extends Entity
{
    public function __construct()
    {
        $this->setTable('metrics_visits');

        $this->addProperties([
            'clients'   => ['default' => 0],
            'sessions'  => ['default' => 0],
            'views'     => ['default' => 0],
            'platform'  => [],
            'adwords_id'=> [],
            'date'      => [],
        ]);
    }
    
    public function loadFromCookie()
    {
        if(empty($_COOKIE['ad_company']) && empty($_COOKIE['ad_source'])) {
            return false;
        }

        $this->select()->where([
            'company' => $_COOKIE['ad_company'],
            'source'  => $_COOKIE['ad_source'],
        ]);

        return $this->load();
    }
}