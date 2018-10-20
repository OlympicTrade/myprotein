<?php

namespace Events\Service;

use Aptero\Db\Entity\EntityFactory;
use Aptero\Service\AbstractService;
use Events\Model\Event;
use User\Service\AuthService;
use Aptero\Db\ResultSet\ResultSet;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class EventsService extends AbstractService
{
    public function deleteEvents($tag)
    {
        $user = AuthService::getUser();

        $events = EntityFactory::collection(new Event());
        $events->addFilter('key', $tag)
            ->addFilter('user_id', $user->getId())
            ->load();
        $events->remove();
    }

    public function deleteEvent($eventId)
    {
        if(!$eventId) {
            return false;
        }

        $authService = new AuthService();
        $user = $authService->getIdentity();

        $event = new Event();
        $event->setId($eventId)
            ->addFilter(array('user_id' => $user->getId()));

        if(!$event->load()) {
            return false;
        }

        return $event->remove();
    }

    public function getEventsList($page, $rows) {
        $rows = min(100, max($rows, 10));

        $authService = new AuthService();
        $user = $authService->getIdentity();

        $event = new Event();
        $events = $event->getCollection();

        $events->setSort('t.id DESC');
        $events->setCols(array('text', 'title', 'type', 'time_create', 'url'));
        $events->addFilter('user_id', $user->getId());

        $select = $events->getSelect();

        $resultSet = new ResultSet();
        $resultSet->setPrototype(clone $event);
        $paginatorAdapter = new DbSelect(
            $select,
            $event->getDbAdapter(),
            $resultSet
        );

        $paginator = new Paginator($paginatorAdapter);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($rows);

        return $paginator;
    }
}