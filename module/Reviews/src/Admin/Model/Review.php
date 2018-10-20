<?php
namespace ReviewsAdmin\Model;

use Aptero\Db\Entity\Entity;
use Zend\Session\Container as SessionContainer;

class Review extends Entity
{
    const STATUS_NEW       = 0;
    const STATUS_VERIFIED  = 1;
    const STATUS_REJECTED  = 2;

    static public $statuses = array(
        self::STATUS_NEW        => 'Новый',
        self::STATUS_VERIFIED   => 'Проверен',
        self::STATUS_REJECTED   => 'Отклонен',
    );

    public function __construct()
    {
        $this->setTable('reviews');

        $this->addProperties(array(
            'user_id'       => array(),
            'name'          => array(),
            'review'        => array(),
            'answer'        => array(),
            'status'        => array(),
            'time_create'   => array(),
        ));

        $this->addPlugin('user', function($model) {
            $catalog = new \UserAdmin\Model\User();
            $catalog->setId($model->get('user_id'));

            return $catalog;
        }, array('independent' => true));
    }
}