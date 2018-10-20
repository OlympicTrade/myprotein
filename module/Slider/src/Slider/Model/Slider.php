<?php
namespace Slider\Model;

use Aptero\Db\Entity\Entity;

class Slider extends Entity
{

    public function __construct()
    {
        $this->setTable('slider');

        $this->addProperties(array(
            'url'         => array(),
            'title'       => array(),
            'desc'        => array(),
            'color'       => array(),
            'btn'         => array(),
            'sort'        => array(),
        ));

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('slider_images');
            $image->setFolder('slider');
            $image->addResolutions(array(
                'm' => array(
                    'width'  => 1920,
                    'height' => 400,
                    'crop'   => true
                ),
                'mb' => array(
                    'width'  => 980,
                    'height' => 180,
                    'crop'   => true
                )
            ));

            return $image;
        });
    }
}