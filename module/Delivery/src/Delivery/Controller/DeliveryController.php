<?php
namespace Delivery\Controller;

use Aptero\Mvc\Controller\AbstractActionController;
use Delivery\Model\City;
use Delivery\Model\Delivery;
use Delivery\Model\Point;
use Delivery\Model\Region;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class DeliveryController extends AbstractActionController
{
    public function updateDeliveryAction()
    {
    	
        echo $this->getGlavpunktService()->fullUpdate();
        die('END');
    }

    public function regionsAction()
    {
        if(!$this->getRequest()->isXmlHttpRequest()) {
            return $this->send404();
        }

        $regions = Region::getEntityCollection();
        $regions->select()
            ->order('priority DESC')
            ->order('cities DESC')
            ->where
                ->notEqualTo('cities', 0);

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array(
            'regions'  => $regions,
            'reload'   => $this->params()->fromQuery('reload', true),
        ));

        return $view;
    }

    public function getCitiesAction()
    {
        if(!$this->getRequest()->isXmlHttpRequest()) {
            return $this->send404();
        }

        $regionId = (int) $this->params()->fromPost('rid');

        $cities = City::getEntityCollection();
        $cities->select()
            ->order('priority DESC')
            ->order('points DESC')
            ->where
                ->equalTo('region_id', $regionId)
                //->equalTo('shoplogistic', 1)
                ->lessThanOrEqualTo('delivery_income', City::$maxDeliveryPrice)
                ->nest()
                    ->notEqualTo('pickup_delay', 0)
                    ->or
                    ->notEqualTo('delivery_delay', 0)
                ->unnest();

        $html = '';
        foreach ($cities as $city) {
            $html .= '<div class="row" data-name="' . $city->get('name') . '" data-id="' . $city->getId() . '">' . $city->get('name') . '</div>';
        }

        if(!$html) {
            $html = '<div class="empty">Ничего не найдено</div>';
        }

        return new JsonModel(['html' => $html]);
    }

        public function citiesSearchAction()
    {
        if(!$this->getRequest()->isXmlHttpRequest()) {
            return $this->send404();
        }

        $query = $this->params()->fromPost('q');

        $cities = City::getEntityCollection();
        $cities->select()
            ->limit(50)
            ->order('priority DESC')
            ->order('delivery_delay')
            ->where
                ->like('name', '%' . $query . '%')
                ->lessThanOrEqualTo('delivery_income', City::$maxDeliveryPrice)
                ->nest()
                    ->nest()
                        ->greaterThan('delivery_income', 0)
                        ->or
                        ->greaterThan('delivery_delay', 0)
                    ->unnest()
                    ->or
                    ->nest()
                        ->greaterThan('pickup_income', 0)
                        ->or
                        ->greaterThan('pickup_delay', 0)
                    ->unnest()
                ->unnest();

        $html = '';
        foreach ($cities as $city) {
            $html .= '<div class="row" data-name="' . $city->get('name') . '" data-id="' . $city->getId() . '">' . $city->get('name') . '</div>';
        }

        if(!$html) {
            $html = '<div class="empty">Город не найден</div>';
        }

        return new JsonModel(['html' => $html]);
    }

    public function pointsMapDataAction()
    {
        return new JsonModel($this->getDeliveryService()->getPointsData($_POST));
    }

    public function citiesMapDataAction()
    {
        return new JsonModel($this->getDeliveryService()->getCitiesData());
    }
    
    public function deliveryNoticeAction()
    {
        if(!$this->getRequest()->isXmlHttpRequest()) {
            return $this->send404();
        }
        
        $cartService = $this->getCartService();
        $price = $cartService->getCartPrice($cartService->getCookieCart());

        $helper = $this->getViewHelper('deliveryNotice');
        
        return new JsonModel([
            'html'  => $helper($price)
        ]);
    }
    
    public function indexAction()
    {
        $this->generate();
        $view = new ViewModel();

        $request = $this->getRequest();

        if($request->isXmlHttpRequest()) {
            $view->setTerminal(true);
        }

        $view->setVariables(array(
            'ajax'     => $request->isXmlHttpRequest(),
            'delivery' => Delivery::getInstance(),
            'header'   => $this->layout()->getVariable('header'),
            'breadcrumbs'   => $this->getBreadcrumbs(),
        ));

        return $view;
    }

    public function rupostCalcAction()
    {
        if(!$this->getRequest()->isXmlHttpRequest()) {
            return $this->send404();
        }
        
        $index = $this->params()->fromPost('index');
        $weight = $this->getCartService()->getCartWeight();
        $price = $this->getCartService()->getCartPrice();

        $data = [
            'weight'      => $weight + 200,
            'summ'        => $price,
            'from_index'  => '198264',
            'to_index'    => $index,
        ];

        $str = '';
        foreach ($data as $key => $val) {
            $str .= '&' . $key . '=' . $val;
        }
        $str = ltrim($str, '&');

        $url = 'http://api.print-post.com/api/sendprice/v2/?' . $str;

        $resp = \Zend\Json\Json::decode(file_get_contents($url));

        $price = $resp->posilka_price_nds;

        return new JsonModel(['price' => ceil($price / 10) * 10]);
    }

    public function pointsAction()
    {
        if(!$this->getRequest()->isXmlHttpRequest()) {
            return $this->send404();
        }

        $type = $this->params()->fromQuery('type', ''); //type = view значит без кнопки "выбрать точку"
        $pointId = $this->params()->fromQuery('pid', 0);

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array(
            'delivery' => Delivery::getInstance(),
            'type'     => $type,
            'pointId'  => $pointId,
        ));

        return $view;
    }

    public function pointInfoAction()
    {
        $point = new Point();

        $point->setId($this->params()->fromPost('id'));

        if(!$point->load()) {
            return $this->send404();
        }

        return new JsonModel(array(
            'id'        => $point->getId(),
            'address'   => $point->get('address'),
        ));
    }

    /**
     * @return \Delivery\Service\DeliveryService
     */
    public function getDeliveryService()
    {
        return $this->getServiceLocator()->get('Delivery\Service\DeliveryService');
    }

    /**
     * @return \Delivery\Service\ParserService
     */
    public function getParserService()
    {
        return $this->getServiceLocator()->get('Delivery\Service\ParserService');
    }

    /**
     * @return \Catalog\Service\CartService
     */
    protected function getCartService()
    {
        return $this->getServiceLocator()->get('Catalog\Service\CartService');
    }

    /**
     * @return \Delivery\Service\GlavpunktService
     */
    public function getGlavpunktService()
    {
        return $this->getServiceLocator()->get('Delivery\Service\GlavpunktService');
    }
}