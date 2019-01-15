<?php
namespace Catalog\View\Helper;
use Aptero\Cache\Feature\GlobalAdapterFeature as StaticCacheAdapter;

use Catalog\Model\Catalog;
use Zend\Uri\Uri;
use Zend\View\Helper\AbstractHelper;

class CatalogMenu extends AbstractHelper
{
    protected $options = [];
    
    public function __invoke($options = array())
    {
        $this->options = array_merge([
            'ul'    => true,
            'sub'   => true,
        ], $options);

        $cacheName = crc32(serialize($this->options));

        $cache = StaticCacheAdapter::getStaticAdapter('html');

        if($html = $cache->getItem($cacheName)) {
            //return $html;
        }

        $catalog = Catalog::getEntityCollection();
        $catalog->setParentId(0);
		$catalog->select()->where(array('active' => 1));

        $html = $this->catalog($catalog);

        $cache->setItem($cacheName, $html);
        $cache->setTags($cacheName, [$catalog->table()]);

        return $html;
    }

    protected function catalog($catalog)
    {
        $html = $this->options['ul'] ? '<ul>' : '';

        $catalog->load();

        foreach($catalog as $category) {
            $sub = $this->catalogSub($category);
            $class = '';

            $url = (new \Zend\Uri\Uri($_SERVER['REQUEST_URI']))->getPath();

            if($url == $category->getUrl()) {
                $class .= 'active';
                $result['active'] = true;
            } elseif($sub['active']) {
                $class .= 'sub-active';
                $result['active'] = true;
            }

            $html .=
                '<li class="' . $class . '">'
                    .'<a href="' . $category->getUrl() . '">' . $category->get('name') . '</a>'
                    . $sub['html']
                .'</li>';
        }

        $html .= $this->options['ul'] ? '</ul>' : '';

        return $html;
    }

    protected function catalogSub(Catalog $parent)
    {
        $types = $parent->getPlugin('types');

        $result = [
            'html'   => '',
            'active' => false
        ];

        if(!$types->count() || !$this->options['sub']) {
            return $result;
        }

        $html = '<ul class="sub">';

        $url = (new Uri($_SERVER['REQUEST_URI']))->getPath();

        foreach($types as $type) {
            if($url == $type->getUrl()) {
                $class = ' class="active"';
                $result['active'] = true;
            } else {
                $class = '';
            }

            $html .=
                '<li' . $class . '>'
                    .'<a href="' . $type->getUrl() . '">' . $type->get('name') . '</a>'
                .'</li>';
        }

        $html .= '</ul>';

        $result['html'] = $html;

        return $result;

        /*$catalog = $parent->getChildren();

        $result = [
            'html'   => '',
            'active' => false
        ];

        if(!$catalog->count() || !$this->options['sub']) {
            return $result;
        }

        $html = '<ul class="sub">';


        $url = (new Uri($_SERVER['REQUEST_URI']))->getPath();
        
        foreach($catalog as $category) {
            $sub = $this->catalogSub($category);

            if($url == $category->getUrl() || $sub['active']) {
                $class = ' class="active"';
                $result['active'] = true;
            } elseif($sub['active']) {
                $class = ' class="sub-active"';
                $result['active'] = true;
            } else {
                $class = '';
            }

            $html .=
                '<li' . $class . '>'
                    .'<a href="' . $category->getUrl() . '">' . $category->get('name') . '</a>'
                    . $sub['html']
                .'</li>';
        }

        $html .= '</ul>';

        $result['html'] = $html;

        return $result;*/
    }
}