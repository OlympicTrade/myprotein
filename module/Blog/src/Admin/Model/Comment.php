<?php
namespace BlogAdmin\Model;

use Aptero\Db\Entity\EntityHierarchy;

class Comment extends EntityHierarchy
{
    const STATUS_NEW       = 0;
    const STATUS_VERIFIED  = 1;
    const STATUS_REJECTED  = 2;

    static public $statuses = array(
        self::STATUS_NEW      => 'Новый',
        self::STATUS_VERIFIED => 'Проверен',
        self::STATUS_REJECTED => 'Отклонен',
    );

    public function __construct()
    {
        $this->setTable('articles_comments');

        $this->addProperties(array(
            'article_id'    => array(),
            'user_id'       => array(),
            'parent'        => array(),
            'name'          => array(),
            'comment'       => array(),
            'status'        => array(),
            'time_create'   => array(),
        ));

        $this->addPlugin('article', function($model) {
            $catalog = new Article();
            $catalog->setId($model->get('article_id'));

            return $catalog;
        }, array('independent' => true));

        $this->addPlugin('user', function($model) {
            $catalog = new \UserAdmin\Model\User();
            $catalog->setId($model->get('user_id'));

            return $catalog;
        }, array('independent' => true));
    }
}