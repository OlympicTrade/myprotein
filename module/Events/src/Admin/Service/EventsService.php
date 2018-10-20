<?php
namespace EventsAdmin\Service;

use Aptero\Db\Entity\EntityFactory;
use Aptero\Service\Admin\TableService;
use EventsAdmin\Model\Event;
use User\Model\User;

class EventsService extends TableService
{
    public function deleteEvents($tag)
    {
        $events = EntityFactory::collection(new Event());
        $events->select()->where(array(
            'key'   => $tag,
            'user_id'   => User::ADMIN_ID,
        ));

        $events->load();
        $events->remove();
    }

    public function deleteEvent($eventId)
    {
        if(!$eventId) {
            return false;
        }

        $event = new Event();
        $event->setId($eventId)
            ->addFilter(array('user_id' => User::ADMIN_ID));

        if(!$event->load()) {
            return false;
        }

        return $event->remove();
    }

    public function getEventsList($page) {
        $event = new Event();
        $events = $event->getCollection();

        $events->select()->order('t.id DESC');
        $events->select()->columns(array('text', 'title', 'type', 'time_create', 'url'));

        return $events->getPaginator($page);
    }
}