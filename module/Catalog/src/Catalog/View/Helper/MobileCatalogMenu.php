<?php
namespace Catalog\View\Helper;
use Aptero\Cache\Feature\GlobalAdapterFeature as StaticCacheAdapter;

use Catalog\Model\Catalog;
use Zend\View\Helper\AbstractHelper;

class MobileCatalogMenu extends AbstractHelper
{
    protected $options = [];
    
    public function __invoke($options = [])
    {
        $this->options = array_merge([
            'ul'       => false,
            'sub'      => true,
            'catalog'  => null,
        ], $options);

        $cacheName = crc32(serialize($this->options));

        $cache = StaticCacheAdapter::getStaticAdapter('html');

        /*if($html = $cache->getItem($cacheName)) {
            return $html;
        }*/

        if(!($catalog = $this->options['catalog'])) {
            $catalog = Catalog::getEntityCollection();
            $catalog->setParentId(0);
        }

        $html = $this->catalog($catalog);

        $cache->setItem($cacheName, $html);
        $cache->setTags($cacheName, [$catalog->table()]);

        return $html;
    }

    protected function catalog($catalog)
    {
        $html = $this->options['ul'] ? '<ul>' : '';

        $catalog->load();

        $url = (new \Zend\Uri\Uri($_SERVER['REQUEST_URI']))->getPath();

        foreach($catalog as $category) {
            $sub = $this->catalogSub($category);

            if($url == $category->getUrl()) {
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

        $html .= $this->options['ul'] ? '</ul>' : '';

        return $html;
    }

    protected function catalogTypes(Catalog $category)
    {
        $result = [
            'html'   => '',
            'active' => false
        ];

        $types = $category->getPlugin('types');
        if(!$types->count()) {
            return $result;
        }

        $html =
            '<ul>'
                .'<li class="back"><span>Назад</span></li>';

        foreach($category->getPlugin('types') as $type) {
            $url = $category->getUrl() . $type->get('url') . '/';

            $html .=
                '<li>'
                    .'<a href="' . $url . '">' . $type->get('name') . '</a>'
                 .'</li>';
        }

        $html .= '</ul>';

        $result['html'] = $html;

        return $result;
    }

    protected function catalogSub(Catalog $parent)
    {
        $catalog = $parent->getChildren();

        $result = [
            'html'   => '',
            'active' => false
        ];

        if(!$catalog->count() || !$this->options['sub']) {
            return $result;
            //return $this->catalogTypes($parent);
        }

        $html =
            '<ul>'
                .'<li class="back"><span>Назад</span></li>';

        foreach($catalog as $category) {
            $url = '/catalog/' . $category->getUrl() . '/';

            $sub = $this->catalogSub($category);

            if(!$sub['html']) {
                $sub = $this->catalogTypes($category);
            }

            if($_SERVER['REQUEST_URI'] == $url || $sub['active']) {
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
                    .'<a href="' . $url . '">' . $category->get('name') . '</a>'
                    . $sub['html']
                .'</li>';
        }

        $html .=
            '</ul>';

        $result['html'] = $html;

        return $result;
    }
}