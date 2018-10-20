<?php
namespace Application\Model;

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
            ]);

        $this->setId(1);
    }

    static protected $instance;
    static public function getInstance()
    {
        if(!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}