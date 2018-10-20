<?php
namespace Tests\Controller;

use Aptero\Mail\Mail;
use Aptero\Mvc\Controller\AbstractActionController;

use Delivery\Model\City;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class TestsController extends AbstractActionController
{
    public function indexAction()
    {
        //echo $this->getTestsService()->updateRegions();
        //echo $this->getTestsService()->updateCities();
        //echo $this->getTestsService()->updatePickupPoints('Б');
        //echo $this->getTestsService()->updatePickupPoints('A');
        echo $this->getTestsService()->updatePointsCount();
        //$this->getTestsService()->updatePickupPoints();
        //$this->getTestsService()->updatePointPrice();

        die();
    }

    /**
     * @return \Tests\Service\TestsService
     */
    public function getTestsService()
    {
        return $this->getServiceLocator()->get('Tests\Service\TestsService');
    }
}