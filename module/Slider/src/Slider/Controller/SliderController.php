<?php
namespace Slider\Controller;

use Aptero\Mvc\Controller\AbstractActionController;

use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class SliderController extends AbstractActionController
{
    public function indexAction()
    {
        $this->generate();

        $slider = $this->getSliderService();

        return array(
            'slider' => $slider
        );
    }

    /**
     * @return \Slider\Service\SliderService
     */
    public function getSliderService()
    {
        return $this->getServiceLocator()->get('Slider\Service\SliderService');
    }
}