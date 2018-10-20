<?php
namespace Athletes\Model;

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
            'full_name'   => array('virtual' => true),
        ));

        $this->addPropertyFilterOut('full_name', function($model) {
            return $model->get('name') . ' ' . $model->get('surname');
        });

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('athletes_images');
            $image->setFolder('athletes');
            $image->addResolutions(array(
                'm' => array(
                    'width'  => 770,
                    'height' => 458,
                    'crop'   => true
                ),
            ));

            return $image;
        });

        $this->addPlugin('images', function() {
            $image = new \Aptero\Db\Plugin\Images();
            $image->setTable('athletes_gallery');
            $image->setFolder('athletes_gallery');
            $image->addResolutions(array(
                'm' => array(
                    'width'  => 770,
                    'height' => 458,
                    'crop'   => true
                ),
            ));

            return $image;
        });
    }
}