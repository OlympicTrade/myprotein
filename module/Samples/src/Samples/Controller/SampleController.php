<?php
namespace Samples\Controller;

use Aptero\Mvc\Controller\AbstractActionController;

use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class SamplesController extends AbstractActionController
{
    public function indexAction()
    {
        $this->generate();

        $samples = $this->getSamplesService();

        return [
            'samples' => $samples
        ];
    }

    /**
     * @return \Samples\Service\SamplesService
     */
    public function getSamplesService()
    {
        return $this->getServiceLocator()->get('Samples\Service\SamplesService');
    }
}