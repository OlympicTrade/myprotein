<?php

namespace Aptero\Mvc\Controller;

use Application\Model\Page;
use Application\Model\Settings;
use Contacts\Model\Contacts;
use Zend\View\Model\ViewModel;

abstract class AbstractMobileActionController extends AbstractActionController
{
    public function generate($url = null)
    {
        $sm = $this->getServiceLocator();

        $page = new Page();

        if(empty($url)) {
            $uriParser = new \Zend\Uri\Uri($this->getRequest()->getUri());
            $url = $uriParser->getPath();
        }

        $page->select()->where(array(
            'url' => $url
        ));

        $page->load();

        switch($page->get('layout')) {
            case 3:
                $this->layout('layout/mobile/article');
                break;
            default:
                $this->layout('layout/mobile/main');
                break;
        }

        if($url == '/') {
            $this->layout('layout/mobile/index');
        }

        $header = $page->get('header') ? $page->get('header') : $page->get('name');

        //Canonical
        $canonical = $page->get('url');

        //Meta
        $meta = (object) array(
            'title'        => ($page->get('title') ? $page->get('title') : $header),
            'description'  => $page->get('description'),
            'keywords'     => $page->get('keywords'),
        );

        $contacts = new Contacts();
        $contacts->setId(1);

        $this->layout()->setVariables([
            'route'        => $sm->get('Application')->getMvcEvent()->getRouteMatch(),
            'canonical'    => $canonical,
            'contacts'     => $contacts,
            'settings'     => Settings::getInstance(),
            'breadcrumbs'  => $this->getBreadcrumbs($page),
            'header'       => $header,
            'meta'         => $meta,
        ]);

        return new ViewModel([
            'breadcrumbs'  => $this->getBreadcrumbs($page),
            'header'       => $header,
            'page'         => $page,
            'isAjax'       => $this->getRequest()->isXmlHttpRequest(),
        ]);

        /*$this->layout()->setVariables(array(
            'route'        => $sm->get('Application')->getMvcEvent()->getRouteMatch(),
            'canonical'    => $canonical,
            'contacts'     => $contacts,
            'settings'     => $settings,
            'breadcrumbs'  => $this->getBreadcrumbs($page),
            'page'         => $page,
            'header'       => $header,
            'meta'         => $meta,
            //'uf'           => $uf,
        ));

        return new ViewModel(array(
            'breadcrumbs'  => $this->getBreadcrumbs($page),
            'header'       => $header,
        ));*/
    }
}