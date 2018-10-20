<?php
namespace ApplicationAdmin\Model;

use Aptero\Db\Entity\EntityHierarchy;
use Aptero\Db\Entity\Entity;

class Page extends EntityHierarchy
{
    public function __construct()
    {
        $this->setTable('site_pages');

        $this->addProperties(array(
            'module_id'     => array(),
            'name'          => array(),
            'url'           => array(),
            'redirect_url'  => array(),
            'header'        => array(),
            'title'         => array(),
            'keywords'      => array(),
            'description'   => array(),
            'layout'        => array('default' => 3),
            'parent'        => array(),
            'text'          => array(),
            'active'        => array(),
            'time_update'   => array(),
            'sort'          => array(),
        ));

        $this->addPlugin('image', function() {
            $image = new \Aptero\Db\Plugin\Image();
            $image->setTable('pages_images');
            $image->setFolder('pages');
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

        $this->addPlugin('module', function($model) {
            $module = new \Application\Model\Module();
            $module->setId($model->get('module_id'));

            return $module;
        }, array('independent' => true));

        $this->addPropertyFilterIn('parent', function($model, $parentId) {
            $parentId = (int) $parentId;
            $model->setParentId($parentId);
            return $parentId;
        });

        $this->addPropertyFilterIn('parent', function($model, $url) {
            return \Aptero\String\Translit::url($url);
        });

        $this->addPropertyFilterIn('url', function($model, $url) {
            if($model->get('module_id')) {
                return $model->get('url');
            }

            return $url;
        });

        //URL
        $this->getEventManager()->attach(array(Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE), function ($event) {
            $model = $event->getTarget();

            $url = $model->get('url');

            if(!$url) {
                $url = \Aptero\String\Translit::url($model->get('name'));
            }

            $url = '/' . trim($url, '/') . '/';
            $url = $url != '//' ? $url : '/';

            $model->set('url', $url);

            return true;
        });

        $this->addPlugin('content', function($model) {
            $content = Content::getEntityCollection();
            $content->select()
                ->where(array('depend' => $model->getId()))
                ->order('t.sort');

            return $content;
        });

        //URL Path
        /*$this->getEventManager()->attach(array(Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE), function ($event) {
            $model = $event->getTarget();

            $url_path = $model->get('url');
            $parent = $model->getParent();
            while($parent) {
                $url_path = $parent->get('url') . '/' . $url_path;
                $parent = $parent->getParent();
            }

            $url_path = !$url_path ? '/' : '/' . $url_path . '/';

            $model->set('url_path', trim($url_path));

            return true;
        });*/
    }
}