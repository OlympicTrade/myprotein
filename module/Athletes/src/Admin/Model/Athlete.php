<?php
namespace AthletesAdmin\Model;

use Aptero\Db\Entity\Entity;

class Athlete extends Entity
{
    public function __construct()
    {
        $this->setTable('athletes');

        $this->addProperties(array(
            'name'        => array(),
            'surname'     => array(),
            'url'         => array(),
            'sport'       => array(),
            'text'        => array(),
            'tags'        => array(),
            'sort'        => array(),
            'title'       => array(),
            'description' => array(),
            'keywords'    => array(),
            'video_1'     => array(),
            'video_2'     => array(),
            'video_3'     => array(),
        ));

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('athletes_images');
            $image->setFolder('athletes');
            $image->addResolutions(array(
                'a' => array(
                    'width'  => 162,
                    'height' => 162,
                ),
                'hr' => array(
                    'width'  => 1000,
                    'height' => 800,
                )
            ));

            return $image;
        });

        $this->addPlugin('images', function() {
            $image = new \Aptero\Db\Plugin\Images();
            $image->setTable('athletes_gallery');
            $image->setFolder('athletes_gallery');
            $image->addResolutions(array(
                'a' => array(
                    'width'  => 162,
                    'height' => 162,
                ),
                'hr' => array(
                    'width'  => 1000,
                    'height' => 800,
                )
            ));

            return $image;
        });

        $this->getEventManager()->attach(array(Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE), function ($event) {
            $model = $event->getTarget();

            if(!$model->get('url')) {
                $model->set('url', \Aptero\String\Translit::url($model->get('name') . ' ' . $model->get('surname')));
            }

            return true;
        });
    }
}