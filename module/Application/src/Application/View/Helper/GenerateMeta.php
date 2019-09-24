<?php
namespace Application\View\Helper;

use Application\Model\Menu;
use Application\Model\MenuItems;
use Application\Model\Settings;
use Zend\View\Helper\AbstractHelper;

class GenerateMeta extends AbstractHelper
{
    protected $currentUrl = null;

    public function __construct()
    {
        $this->currentUrl = \Aptero\Url\Url::getUrl([], [], null, true);
    }

    public function __invoke($mobile = false)
    {
        $view = $this->getView();
        $meta = $view->meta;
        $settings = Settings::getInstance();

        $view->headTitle($meta->title);

        $view->headMeta()
            ->appendProperty('og:site_name', 'Myprotein')
            ->appendProperty('og:locale', 'ru_RU')

            ->appendName('theme-color', '#0060c1')
            ->appendName('description', $meta->description)

            ->appendName('msapplication-TileImage', '/images/logos/144.png')
            ->appendName('msapplication-TileColor', '#0060c1')

            ->appendName('msapplication-config', '/browserconfig.xml')
            ->appendName('msapplication-tooltip', 'Myprotein')
        ;

        $view->headLink()
            ->appendAlternate(['rel' => 'shortcut icon', 'href' => '/images/favicon.ico'])

            ->appendAlternate(['rel' => 'image_src', 'href' => '/images/logos/soc.png'])

            ->appendAlternate(['rel' => 'icon', 'type' => 'image/png', 'href' => '/images/logos/16.png', 'sizes' => '16x16'])
            ->appendAlternate(['rel' => 'icon', 'type' => 'image/png', 'href' => '/images/logos/32.png', 'sizes' => '32x32'])
            ->appendAlternate(['rel' => 'icon', 'type' => 'image/png', 'href' => '/images/logos/96.png', 'sizes' => '96x96'])
            ->appendAlternate(['rel' => 'icon', 'type' => 'image/png', 'href' => '/images/logos/192.png', 'sizes' => '192x192'])

            ->appendAlternate(['rel' => 'apple-touch-icon', 'href' => '/images/logos/57.png', 'sizes' => '57x57'])
            ->appendAlternate(['rel' => 'apple-touch-icon', 'href' => '/images/logos/60.png', 'sizes' => '60x60'])
            ->appendAlternate(['rel' => 'apple-touch-icon', 'href' => '/images/logos/72.png', 'sizes' => '72x72'])
            ->appendAlternate(['rel' => 'apple-touch-icon', 'href' => '/images/logos/76.png', 'sizes' => '76x76'])
            ->appendAlternate(['rel' => 'apple-touch-icon', 'href' => '/images/logos/114.png', 'sizes' => '114x114'])
            ->appendAlternate(['rel' => 'apple-touch-icon', 'href' => '/images/logos/120.png', 'sizes' => '120x120'])
            ->appendAlternate(['rel' => 'apple-touch-icon', 'href' => '/images/logos/144.png', 'sizes' => '144x144'])
            ->appendAlternate(['rel' => 'apple-touch-icon', 'href' => '/images/logos/152.png', 'sizes' => '152x152'])

            ->appendAlternate(['rel' => 'manifest', 'href' => '/manifest.json'])
        ;

        if($mobile) {
            $view->headLink()
                ->appendAlternate(array('rel' => 'canonical', 'href' => $settings->get('domain') . $view->canonical));
        } else {
            $view->headLink()
                ->appendAlternate(array('rel' => 'canonical', 'href' => $settings->get('domain') . $view->canonical))
                ->appendAlternate(array('rel' => 'alternative', 'href' => $settings->get('mdomain') . $view->canonical));
        }
    }
}