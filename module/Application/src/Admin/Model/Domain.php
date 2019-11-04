<?php
namespace ApplicationAdmin\Model;

use Aptero\Db\Entity\Entity;
use Mobile_Detect;

class Domain extends Entity
{
    public function __construct()
    {
        $this->setTable('domains');

        $this->addProperties([
            'depend'        => [],
            'domain'        => [],
            'mdomain'       => [],
            'region_name'   => [],
            'city_name'     => [],
            'city_name_r'   => [],
            'city_name_i'   => [],
            'city_name_b'   => [],
            'robots'        => [],
            'html_head'     => [],
            'html_body'     => [],
        ]);
    }

    static protected $instance;
    static public function getInstance()
    {
        if(!self::$instance) {
            $domain = new self();
            $domain->select()->where
                ->nest()
                    ->equalTo('domain', $_SERVER['HTTP_HOST'])
                    ->or
                    ->equalTo('mdomain', $_SERVER['HTTP_HOST'])
                ->unnest();

            if(!$domain->load()) {
                $domain->clear();
                $domain->select()->where(['region_name' => 'Россия']);
            }

            self::$instance = $domain->load();
        }

        return self::$instance;
    }

    public function isGlobal()
    {
        return $this->get('region_name') == 'Россия';
    }

    public function getDomain()
    {
        return (new Mobile_Detect())->isMobile() ? $this->get('mdomain') : $this->get('domain');
    }

    public function loadFromCity($city)
    {
        $this->select()->where(['region_name' => $city->get('name')]);

        if(!$this->load()) {
            $this->clear();
            $this->select()->where(['region_name' => 'Россия']);
            $this->load();
        }

        return $this;
    }
}