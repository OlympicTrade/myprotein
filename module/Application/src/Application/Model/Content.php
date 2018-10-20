<?php
namespace Application\Model;

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
                'm' => array(
                    'width'  => 750,
                    'height' => 420,
                ),
                's' => array(
                    'width'  => 400,
                    'height' => 225,
                    'crop'   => true
                ),
            ));

            return $image;
        });
    }
}