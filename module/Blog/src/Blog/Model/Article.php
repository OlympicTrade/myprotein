<?php
namespace Blog\Model;

use Application\Model\Content;
use Aptero\Db\Entity\Entity;

class Article extends Entity
{
    public function __construct()
    {
        $this->setTable('articles');

        $this->addProperties(array(
            'name'        => [],
            'preview'     => [],
            'text'        => [],
            'tags'        => [],
            'url'         => [],
            'title'       => [],
            'description' => [],
            'keywords'    => [],
            'time_create' => [],
        ));

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('articles_images');
            $image->setFolder('articles');
            $image->addResolutions(array(
                'm' => array(
                    'width'  => 750,
                    'height' => 420,
                    'crop'   => true
                ),
                's' => array(
                    'width'  => 400,
                    'height' => 225,
                    'crop'   => true
                ),
                'vs' => array(
                    'width'  => 85,
                    'height' => 45,
                    'crop'   => true
                ),
            ));

            return $image;
        });

        $this->addPlugin('content', function($model) {
            $content = Content::getEntityCollection();
            $content->select()
                ->where(array('depend' => $model->getId()))
                ->order('t.sort');

            return $content;
        });

        $this->addPlugin('comments', function($model) {
            $catalog = Comment::getEntityCollection();
            $catalog->select()
                ->where(array(
                    'article_id' => $model->getId(),
                    'status'     => Comment::STATUS_VERIFIED,
                ))
                ->order('time_create DESC');
            $catalog->setParentId(0);;

            return $catalog;
        });
    }

    public function getUrl()
    {
        return '/blog/' . $this->get('url');
    }
}