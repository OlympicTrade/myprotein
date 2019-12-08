<?php

namespace Aptero\Mvc\Controller;

use Application\Model\Page;
use Application\Model\Region;
use Application\Model\Settings;
use ApplicationAdmin\Model\Domain;
use Contacts\Model\Contacts;
use Delivery\Model\City;
use Mobile_Detect;
use Zend\Mvc\Controller\AbstractActionController as ZendActionController;
use Zend\View\Model\ViewModel;

abstract class AbstractActionController extends ZendActionController
{
    protected function regionRedirect()
    {
        $redirect = function($url, $code = '302') {
            //dd($url);
            header('Location: https://' . $url);
            header('HTTP/1.1 ' . $code);
            die();
        };

        $cDomain = Domain::getInstance();

        if($cDomain->isGlobal() && $_GET['city'] && $nCity = (new City())->setId($_GET['city'])->load()) {
            setcookie('city', $nCity->getId(), time() + 3600, '/');
            $redirect($cDomain->getDomain() . $this->getRequest()->getUri()->getPath());
        }

        $aCity = \Delivery\Model\Delivery::getInstance()->getCity();

        $aDomain = (new Domain)->loadFromCity($aCity);

        if($cDomain->getId() != $aDomain->getId()) {
            $redirect(
                $aDomain->getDomain() .
                $_SERVER['REQUEST_URI'] .
                ($aDomain->isGlobal() ? '?city=' . $aCity->getId() : '')
            );
        }
    }

    /**
     * @param null $url
     * @return ViewModel
     */
    public function generate($url = null)
    {
        $settings = Settings::getInstance();

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
                $this->layout('layout/article');
                break;
            default:
                $this->layout('layout/main');
                break;
        }

        if($url == '/') {
            $this->layout('layout/index');
        }

        $header = $page->get('header') ? $page->get('header') : $page->get('name');

        //Canonical
        $canonical = $page->get('url');

        //Meta
        /*$meta = (object) [
            'title'        => $page->get('title') ? $page->get('title') : $header,
            'description'  => $page->get('description'),
            'keywords'     => $page->get('keywords'),
        ];*/
        $meta = $this->generateMeta($page);

        $this->generateMeta($page, [], []);

        $contacts = new Contacts();
        $contacts->setId(1);

        $this->layout()->setVariables([
            'route'        => $sm->get('Application')->getMvcEvent()->getRouteMatch(),
            'canonical'    => $canonical,
            'contacts'     => $contacts,
            'settings'     => $settings,
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
    }

    protected function send404()
    {
        $this->generate();

		$meta = (object) array(
            'title'        => 'Страница не найдена',
            'description'  => 'Страница не найдена',
            'keywords'     => '',
        );
		
		$this->layout()->setVariable('meta', $meta);
		
        $response = $this->getResponse();
        $response->setStatusCode(404);
        $response->sendHeaders();

        return;
    }

    /**
     * @param $entity
     * @param $search
     * @param $replace
     * @param array $options
     * @return \stdClass
     */
    protected function generateMeta($entity, $search = [], $replace = [], $options = [])
    {
        $options = array_merge(array('prefix' => ''), $options);

        $search = (array) $search;
        $search[] ='{CITY_NAME}';   //Москва
        $search[] ='{CITY_NAME_R}'; //Москве
        $search[] ='{CITY_NAME_I}'; //в Москве
        $search[] ='{CITY_NAME_B}'; //по Москве

        $settings = Settings::getInstance();
        $replace[] = $settings->get('city_name');
        $replace[] = $settings->get('city_name_r');
        $replace[] = $settings->get('city_name_i');
        $replace[] = $settings->get('city_name_b');
        //dd($replace);

        $meta = new \stdClass();
        $settings = $this->getServiceLocator()->get('Application\Model\Module')->getPlugin('settings');

        //Title
        if(!$entity || !$entity->get('title')) {
            $pattern = !empty($options['title']) ? $options['title'] : $settings->get($options['prefix'] . 'title');
        } else {
            $pattern = $entity->get('title');
        }
        $meta->title = str_replace($search, $replace, $pattern);

        $page = (int) $this->params()->fromQuery('page', 1);
        if($page > 1) {
            $meta->title .= ' — страница ' . $page;
        }

        //Keywords
        if(!$entity || !$entity->get('keywords')) {
            $pattern = !empty($options['keywords']) ? $options['keywords'] : $settings->get($options['prefix'] . 'keywords');
        } else {
            $pattern = $entity->get('keywords');
        }
        $meta->keywords = str_replace($search, $replace, $pattern);

        //Description
        if(!$entity || !$entity->get('description')) {
            $pattern = !empty($options['description']) ? $options['description'] : $settings->get($options['prefix'] . 'description');
        } else {
            $pattern = $entity->get('description');
        }
        $meta->description = str_replace($search, $replace, $pattern);

        $this->layout()->setVariable('meta', $meta);

        return $meta;
    }

    protected function addBreadcrumbs($crumbs)
    {
        $breadcrumbs = array_merge($this->getBreadcrumbs(), $crumbs);

        $this->layout()->setVariable('breadcrumbs', $breadcrumbs);

        return $breadcrumbs;
    }

    /**
     * @param Page $page
     * @return array
     */
    protected function getBreadcrumbs($page = null)
    {
        if(!$page) {
            return $this->layout()->getVariable('breadcrumbs');
        }

        $breadcrumbs = array();

        do {
            $breadcrumbs[] = array('url' => $page->get('url'), 'name' => $page->get('name'));
        } while ($page = $page->getParent());

        $breadcrumbs[] = array('url' => '/', 'name' => 'Home');

        return array_reverse($breadcrumbs);
    }

    /**
     * @param $helperName
     * @param null $helperParam
     * @return string
     */
    public function viewHelper($helperName, $helperParam = null)
    {
        $helper = $this->getViewHelper($helperName);
        return $helper($helperParam);
    }

    public function getViewHelper($helperName)
    {
        return $this->getSL()->get('ViewHelperManager')->get($helperName);
    }

    /**
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getSL()
    {
        return $this->getServiceLocator();
    }
}