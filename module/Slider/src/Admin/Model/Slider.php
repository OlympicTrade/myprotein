<?php
namespace SliderAdmin\Model;

use Aptero\Db\Entity\Entity;
use Zend\Session\Container as SessionContainer;

class Slider extends Entity
{

    public function __construct()
    {
        $this->setTable('slider');

        $this->addProperties(array(
            'sort'        => array(),
            'url'         => array(),
            'title'       => array(),
            'desc'        => array(),
            'color'       => array('color' => '#ffffff'),
            'active'      => array('default' => 1),
            'btn'         => array(),
        ));

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('slider_images');
            $image->setFolder('slider');
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