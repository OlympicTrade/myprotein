<?php
namespace Mainpage\Model;

use Aptero\Db\Entity\Entity;

class Mainpage extends Entity
{

    public function __construct()
    {
        $this->setTable('main_page');

        $this->addProperties(array(
            'logo_desc'     => array(),
            'slider_title'  => array(),
            'slider_text'   => array(),
            'events_title'  => array(),
            'text_title'    => array(),
            'text_desc'     => array(),
            'adv_1_text'    => array(),
            'adv_2_text'    => array(),
            'adv_3_text'    => array(),
            'adv_4_text'    => array(),
        ));
    }
}