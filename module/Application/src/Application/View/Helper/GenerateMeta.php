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
        $this->currentUrl = \Aptero\Url\Url::getUrl([], [], null, true);
    }

    public function __invoke($mobile = false)
    {
        $view = $this->getView();
        $meta = $view->meta;

        $view->headTitle($meta->title);

        $view->headMeta()
            //->appendName('description', 'asd');
            ->appendName('description', $meta->description);

        $view->headLink()
            ->appendAlternate(array('rel' => 'shortcut icon', 'href' => '/images/favicon.ico'));
			
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