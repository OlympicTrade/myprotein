<?php
namespace Events\Controller;

use Aptero\Mvc\Controller\AbstractActionController;

use Events\Model\Event;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class EventsController extends AbstractActionController
{
    public function indexAction()
    {
        $page = $this->params()->fromQuery('page');
        $rows = $this->params()->fromQuery('rows');

        $this->generate();

        $events = $this->getEventsService()->getEventsList($page, $rows);

        return array(
            'events' => $events
        );
    }

    public function delEventAction()
    {
        $request = $this->getRequest();

        if(!$request->isXmlHttpRequest()) {
            return $this->send404();
        }

        $eventId = $this->params()->fromPost('id', 0);

        $status = $this->getEventsService()->deleteEvent($eventId);

        $jsonModel = new JsonModel(array(
            'status' => (int) $status
        ));

        return $jsonModel;
    }

    /**
     * @return \Events\Service\EventsService
     */
    public function getEventsService()
    {
        return $this->getServiceLocator()->get('Events\Service\EventsService');
    }
}