<?php
namespace ApplicationAdmin\Model;

use Aptero\Db\Entity\Entity;

class Content extends Entity
{

    public function __construct()
    {
        $this->setTable('content');

        $this->addProperties(array(
            'depend'      => array(),
            'module'      => array(),
            'title'       => array(),
            'text'        => array(),
            'video'       => array(),
            'type'        => array(),
            'sort'        => array(),
        ));

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('content_images');
            $image->setFolder('content');
            $image->addResolutions(array(
                'a' => array(
                    'width'  => 120,
                    'height' => 120,
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
            $image->setTable('content_gallery');
            $image->setFolder('content_gallery');
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

            if($model->get('sort')) {
                return true;
            }

            $content = new Content();
            $content->select()->where(array(
                'depend'    => $model->get('depend'),
                'module'    => $model->get('module'),
            ))->order('sort DESC');

            if($content->load()) {
                $model->set('sort', $content->get('sort') + 5);
            } else {
                $model->set('sort', 5);
            }

            return true;
        });
    }
}