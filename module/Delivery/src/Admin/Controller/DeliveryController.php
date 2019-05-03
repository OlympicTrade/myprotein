<?php
namespace DeliveryAdmin\Controller;

use Aptero\Delivery\Glavpunkt;
use Aptero\Mvc\Controller\Admin\AbstractActionController;
use DeliveryAdmin\Model\City;
use DeliveryAdmin\Model\Delivery;
use DeliveryAdmin\Model\Point;
use Zend\View\Model\JsonModel;

class DeliveryController extends AbstractActionController
{
    public function getPointInfoAction()
    {
        $pointId = $this->params()->fromPost('pointId');

        $point = new Point();
        $point->setId($pointId);

        $companies = [];

        if($point->get('glavpunkt')) {
            $companies[Delivery::COMPANY_GLAVPUNKT] = Delivery::$deliveryCompanies[Delivery::COMPANY_GLAVPUNKT];
        }

        if($point->get('index_express')) {
            $companies[Delivery::COMPANY_INDEX_EXPRESS] = Delivery::$deliveryCompanies[Delivery::COMPANY_INDEX_EXPRESS];
        }

        $companies[Delivery::COMPANY_UNKNOWN] = Delivery::$deliveryCompanies[Delivery::COMPANY_UNKNOWN];

        return new JsonModel([
            'companies' => $companies
        ]);
    }

    public function clearInvoiceAction()
    {
        $gp = new Glavpunkt();
        $gp->delInvoice();

        return new JsonModel(['status' => true]);
    }

    public function getCitiesAction()
    {
        $deliveryType = $this->params()->fromPost('deliveryType');

        $cities = City::getEntityCollection();

        if($deliveryType == Delivery::TYPE_COURIER) {
            $cities->select()->where
                ->notEqualTo('delivery_income', 0)
                ->notEqualTo('delivery_delay', 0);
        }

        if ($deliveryType == Delivery::TYPE_PICKUP) {
            $cities->select()->where
                ->notEqualTo('pickup_income', 0)
                ->notEqualTo('pickup_delay', 0);
        }

        $resp = [];

        foreach($cities as $city) {
           $resp[] = $city->get('name');
        }

        return new JsonModel(['cities' => $resp]);
    }

    public function getPointsAction()
    {
        $cityId = $this->params()->fromPost('cityId');

        $points = Point::getEntityCollection();
        $points->select()->where(['city_id' => $cityId]);

        $resp = [];

        foreach ($points as $point) {
            $resp[$point->getId()] = $point->get('address');
        }

        return new JsonModel(['points' => $resp]);
    }
}