<?php
namespace Application\Model;

use Aptero\Db\Entity\EntityHierarchy;

class Page extends EntityHierarchy
{
    public function __construct()
    {
        $this->setTable('site_pages');

        $this->addProperties(array(
            'name'          => array(),
            'url'           => array(),
            'redirect_url'  => array(),
            'alias'         => array(),
            'header'        => array(),
            'title'         => array(),
            'keywords'      => array(),
            'description'   => array(),
            'layout'        => array(),
            'parent'        => array(),
            'text'          => array(),
            'works_type'    => array(),
            'sitemap'       => array(),
        ));

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('pages_images');
            $image->setFolder('pages');
            $image->addResolutions(array(
                'bg' => array(
                    'width'  => 910,
                    'height' => 320,
                    'crop'   => true,
                )
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
    }
}