<?php
namespace Events\View\Helper;

use User\Service\AuthService;
use Zend\Paginator\Paginator;
use Zend\View\Helper\AbstractHelper;

class EventsList extends AbstractHelper
{
    public function __invoke($events)
    {
        if(!$events->count()) {
            return
            '<div class="std-box">'
                .'У вас пока нет событий'
            .'</div>';
        }

        $types = array(
            'comment' => array('icon' => 'fa-comment-o'),
            'event'   => array('icon' => 'fa-bell'),
        );

        $html =
            '<div class="cols events-list">';

        foreach($events as $event) {
            $date = $this->getView()->dateFormat(\DateTime::createFromFormat('Y-m-d H:i:s', $event->get('time_create')), \IntlDateFormatter::LONG);
            $url = $event->get('url');

            $html .=
                '<div class="col-4">'
                    .'<div class="std-box event">'
                        .'<a href="' . $url . '"><i class="icon fas ' . $types[$event->get('type')]['icon'] . '"></i></a>'
                        .'<div class="info">'
                            .'<a href="' . $url . '" class="title">' . $event->get('title') . '</a>'
                            .'<div class="desc">' . mb_strcut($event->get('text'), 0, 100) . '</div>'
                            .'<div class="date">' . $date . '</div>'
                        .'</div>'
                        .'<span class="del" data-id="' . $event->getId() . '"><i class="far fa-times-circle"></i></span>'
                    .'</div>'
                .'</div>';
        }

        $html .=
            '</div>';

        if(is_a($events, 'Zend\Paginator\Paginator')) {
            $html .= $this->getView()->paginationControl($events, 'Sliding', 'pagination-slide', array('route' => 'application/pagination'));
        }

        return $html;
    }
}