<?php
namespace ApplicationAdmin\Model;

use Aptero\Db\Entity\EntityHierarchy;
use Aptero\Db\Entity\Entity;

class Settings extends EntityHierarchy
{
    public function __construct()
    {
        $this->setTable('site_settings');

        $this->addProperties(array(
            'site_name'   => array(),
            'site_color'  => array(),
            'site_logo'   => array(),
            'domain'      => array(),
            'html_head'   => array(),
            'html_body'   => array(),
            'metriks'     => array(),
            'robots'      => array(),
            'mail_sender'    => array(),
            'mail_email'     => array(),
            'mail_password'  => array(),
            'mail_smtp'   => array(),
        ));

        $this->getEventManager()->attach(array(Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE), function ($event) {
            file_put_contents(PUBLIC_DIR . '/robots.txt', $event->getTarget()->get('robots'));

            return true;
        });

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