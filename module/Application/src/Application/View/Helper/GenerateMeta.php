<?php
namespace Application\View\Helper;

use Application\Model\Menu;
use Application\Model\MenuItems;
use Zend\View\Helper\AbstractHelper;

class GenerateMeta extends AbstractHelper
{
    protected $currentUrl = null;

    public function __construct()
    {
        $this->currentUrl = \Aptero\Url\Url::getUrl(array(), array(), null, true);
    }

    public function __invoke($mobile = false)
    {
        $view = $this->getView();
        $uf = $view->uf;
        $meta = $view->meta;

        $view->headTitle($meta->title);

        $view->headMeta()
            /*->appendProperty('og:title',      $uf->title)
            ->appendProperty('og:description',$uf->description)
            ->appendProperty('og:type',       $uf->type)
            ->appendProperty('og:url',        $uf->url)
            ->appendProperty('og:image',      $uf->image)
            ->appendProperty('og:site_name',  $uf->sitename)
            ->appendProperty('article:tag',   $uf->tags)

            ->appendName('application-name', $uf->sitename)
            ->appendName('msapplication-TileImage', $uf->image)
            ->appendName('msapplication-TileColor', $uf->color)*/
            ->appendName('keywords', $meta->keywords)
            ->appendName('description', $meta->description);

        $view->headLink()
            ->appendAlternate(array('rel' => 'shortcut icon', 'href' => '/images/favicon.ico'));
            /*->appendAlternate(array('rel' => 'apple-touch-icon', 'href' => $uf->image))
            ->appendAlternate(array('rel' => 'image_src', 'href' => $uf->image));*/
			
		if($mobile) {
            $view->headLink()
                ->appendAlternate(array('rel' => 'canonical', 'href' => $view->settings->get('domain') . $view->canonical));
        } else {
            $view->headLink()
                ->appendAlternate(array('rel' => 'canonical', 'href' => $view->settings->get('domain') . $view->canonical))
                ->appendAlternate(array('rel' => 'alternative', 'href' => $view->settings->get('mdomain') . $view->canonical));
        }
    }
}