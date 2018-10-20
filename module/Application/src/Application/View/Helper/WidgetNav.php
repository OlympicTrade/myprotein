<?php
namespace Application\View\Helper;

use Application\Model\Menu;
use Application\Model\MenuItems;
use Zend\View\Helper\AbstractHelper;

class WidgetNav extends AbstractHelper
{
    protected $currentUrl = null;

    public function __construct()
    {
        $this->currentUrl = \Aptero\Url\Url::getUrl(array(), array(), null, true);
    }

    public function __invoke($position = 1, $class = null)
    {
        $menu = new Menu();
        $menu->select()->where(array('position' => $position));
        $menu->load();
        $items = $menu->getPlugin('items')
            ->setParentId(0);

        $items->select()->order('sort');

        $html = $this->menu($items, 0, $class);

        return $html;
    }

    public function menu($items, $depth = 0, $class = null)
    {
        $html =
            '<ul' . ($class ? ' class="' . $class . '"' : '') . '>';

        foreach($items as $item) {
            $children = $item->getChildren();

            if($item->get('type') == MenuItems::TYPE_PAGE) {
                $url = $item->getPlugin('page')->get('url');
            } else {
                $url = $item->get('url');
            }

            $html .=
                '<li>'
                    .'<a href="' . $url . '"' . ($this->currentUrl == $url ? ' class="active"' : '') . '>'
                        . $item->get('name')
                    .'</a>';

            if($children->count()) {
                $html .= $this->menu($children, $depth++);
            }

            $html .=
                '</li>';
        }

        $html .=
            '</ul>';

        return $html;
    }
}