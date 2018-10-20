<?php
namespace BlogAdmin\Model;

use Aptero\Db\Entity\Entity;

class Article extends Entity
{
    public function __construct()
    {
        $this->setTable('articles');

        $tomorrow = new \DateTime();
        $tomorrow->add(new \DateInterval('P1D'));

        $this->addProperties(array(
            'name'        => array(),
            'preview'     => array(),
            'text'        => array(),
            'tags'        => array(),
            'url'         => array(),
            'title'       => array(),
            'links'       => array(),
            'description' => array(),
            'keywords'    => array(),
            'time_update' => array(),
            'time_create' => array('default' => $tomorrow->format('Y-m-d H:i:s')),
        ));

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('articles_images');
            $image->setFolder('articles');
            $image->addResolutions(array(
                'a' => array(
                    'width'  => 120,
                    'height' => 120,
                    'crop'   => true
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
                $model->set('url', \Aptero\String\Translit::url($model->get('name')));
            }

            return true;
        });
    }
}