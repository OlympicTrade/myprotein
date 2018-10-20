<?php
namespace Blog\Model;

use Aptero\Db\Entity\EntityHierarchy;

class Comment extends EntityHierarchy
{
    const STATUS_NEW       = 0;
    const STATUS_VERIFIED  = 1;
    const STATUS_REJECTED  = 2;

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
    }
}