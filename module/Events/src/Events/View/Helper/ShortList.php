<?php
namespace Events\View\Helper;

use Zend\View\Helper\AbstractHelper;

class ShortList extends AbstractHelper
{
    protected $events = null;

    public function __construct($events)
    {
        $this->events = $events;
    }

    public function __invoke()
    {
        if(!$this->events->count()) {
            return
                '';
        }

        $types = array(
            'comment' => array('icon' => 'fa-comment'),
            'event'   => array('icon' => 'fa-bell'),
            'deal'    => array('icon' => 'fa-gavel'),
        );

        $html =
             '<div class="counter">' . $this->events->getTotalItemCount() . '</div>'
            .'<div class="events-short-list">'
                .'<div class="shadow">'
                    .'<div class="header">'
                        .'<i class="fas fa-caret-up"></i>'
                        .'События'
                    .'</div>'
                    .'<div class="list">';

        foreach($this->events as $event) {
            $date = $this->getView()->dateFormat(\DateTime::createFromFormat('Y-m-d H:i:s', $event->get('time_create')), \IntlDateFormatter::LONG);

            $html .=
                '<a class="event" href="' . $event->get('url') . '">'
                    .'<i class="fas ' . $types[$event->get('type')]['icon'] . '"></i>'
                    .'<div class="title">' . $event->get('title') . '</div>'
                    .'<div class="desc">' . $event->get('text') . '</div>'
                    .'<div class="date">' . $date . '</div>'
                .'</a>';
        }

        $html .=
                    '</div>'
                    .'<a class="show-all" href="' . $this->getView()->url('events') . '">'
                        .'Показать все уведомления'
                        .'<i class="fas fa-cog"></i>'
                    .'</a>'
                .'</div>'
            .'</div>';

        return $html;
    }
}