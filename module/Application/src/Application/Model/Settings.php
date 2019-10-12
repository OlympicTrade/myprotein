<?php
namespace Application\Model;

use ApplicationAdmin\Model\Domain;
use Aptero\Db\Entity\Entity;

class Settings extends Entity
{
    public function __construct()
    {
        $this->setTable('site_settings')
            ->enableCache()
            ->addProperties([
                'site_name'      => [],
                'site_color'     => [],
                'site_logo'      => [],
                'domain'         => [],
                'mdomain'        => [],
                'html_head'      => [],
                'html_body'      => [],
                'robots'         => [],
                'mail_sender'    => [],
                'mail_email'     => [],
                'mail_password'  => [],
                'mail_smtp'      => [],

                'city_name'      => [],
                'city_name_r'    => [],
                'city_name_i'    => [],
                'city_name_b'    => [],
            ]);

        $this->setId(1);
    }

    static protected $instance;
    static public function getInstance()
    {
        if(!self::$instance) {
            $settings = self::$instance = (new self())->load();

            $domain = new Domain();
            $domain->select()->where
                ->nest()
                    ->equalTo('domain', $_SERVER['HTTP_HOST'])
                    ->or
                    ->equalTo('mdomain', $_SERVER['HTTP_HOST'])
                ->unnest();

            $domain->load();

            $settings->set('robots', $settings->get('robots') . "\n" . $domain->get('robots'));
            $settings->set('html_head', $settings->get('html_head') . "\n" . $domain->get('html_head'));
            $settings->set('html_head', $settings->get('html_head') . "\n" . $domain->get('html_head'));

            $settings->set('domain', 'https://' . $domain->get('domain') . '/');
            $settings->set('mdomain', 'https://' . $domain->get('mdomain') . '/');

            $settings->set('city_name',   $domain->get('city_name'));
            $settings->set('city_name_r', $domain->get('city_name_r'));
            $settings->set('city_name_i', $domain->get('city_name_i'));
            $settings->set('city_name_b', $domain->get('city_name_b'));
        }

        return self::$instance;
    }
}