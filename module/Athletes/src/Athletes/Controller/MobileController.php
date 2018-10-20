<?php
namespace Athletes\Controller;

use Aptero\Mvc\Controller\AbstractActionController;

use Aptero\Mvc\Controller\AbstractMobileActionController;
use Athletes\Model\Athlete;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\View\View;

class MobileController extends AbstractMobileActionController
{
    public function indexAction()
    {
        $this->generate();

        $athletes = Athlete::getEntityCollection();
        
        return array(
            'athletes' => $athletes
        );
    }

    public function athleteAction()
    {
        $url = $this->params()->fromRoute('url');

        $athlete = new Athlete();
        $athlete->select()->where(array(
            'url' => $url
        ));

        if(!$athlete->load()) {
            return $this->send404();
        }

        if($this->getRequest()->isXmlHttpRequest()) {
            $view = new ViewModel(array(
                'athlete'     => $athlete,
                'ajax'        => true,
            ));
            $view->setTerminal(true);
        } else {
            $view = $this->generate('/athletes/');
            $this->addBreadcrumbs(array(array('url' => '/athletes/' . $athlete->get('url'), 'name' => $athlete->get('full_name'))));

            $view->setVariables(array(
                'athlete'     => $athlete,
                'breadcrumbs' => $this->getBreadcrumbs(),
                'header'      => $athlete->get('full_name'),
                'products'    => array(),
                'ajax'        => false,
            ));
        }

        $this->generate();

        return $view;
    }

    /**
     * @return \Athletes\Service\AthletesService
     */
    public function getAthletesService()
    {
        return $this->getServiceLocator()->get('Athletes\Service\AthletesService');
    }
}