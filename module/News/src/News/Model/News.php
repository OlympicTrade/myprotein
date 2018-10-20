<?php
namespace News\Model;

use Aptero\Db\Entity\Entity;

class News extends Entity
{
    public function __construct()
    {
        $this->setTable('news');

        $this->addProperties(array(
            'name'        => array(),
            'text'        => array(),
            'preview'     => array(),
            'url'         => array(),
            'title'       => array(),
            'description' => array(),
            'keywords'    => array(),
            'author'      => array(),
            'date'        => array('default' => date('Y-m-d')),
            'status'      => array('default' => 1),
            'time_update' => array(),
        ));

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('news_images');
            $image->setFolder('news');
            $image->addResolutions(array(
                's' => array(
                    'width'  => 250,
                    'height' => 140,
                    'crop'   => true,
                ),
                'm' => array(
                    'width'  => 400,
                    'height' => 400,
                )
            ));

            return $image;
        });

        $this->select()->where(['status' => 1]);
        $this->select()->where
            ->lessThanOrEqualTo('time_create', date('Y-m-d H:i:s'));
    }
}