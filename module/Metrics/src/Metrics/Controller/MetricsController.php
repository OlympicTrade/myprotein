<?php
namespace Metrics\Controller;

use Aptero\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class MetricsController extends AbstractActionController
{
    public function initAction()
    {
        $this->getMetricsService()->initMetrics();
        return new JsonModel(['status' => 1]);
    }

    /**
     * @return \Metrics\Service\MetricsService
     */
    public function getMetricsService()
    {
        return $this->getServiceLocator()->get('Metrics\Service\MetricsService');
    }
}