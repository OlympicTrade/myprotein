<?php
namespace NewsAdmin\Model;

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
        ));

        $this->getEventManager()->attach(array(Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE), function ($event) {
            $model = $event->getTarget();

            if(!$model->get('url')) {
                $model->set('url', \Aptero\String\Translit::url($model->get('name')));
            }

            return true;
        });

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('news_images');
            $image->setFolder('news');
            $image->addResolutions(array(
                'a' => array(
                    'width'  => 162,
                    'height' => 162,
                    'crop'   => true,
                ),
                'hr' => array(
                    'width'  => 1000,
                    'height' => 800,
                )
            ));

            return $image;
        });
    }
}