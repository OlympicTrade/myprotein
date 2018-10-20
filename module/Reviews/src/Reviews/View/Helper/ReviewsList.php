<?php
namespace Reviews\View\Helper;

use Zend\View\Helper\AbstractHelper;

class ReviewsList extends AbstractHelper
{
    public function __invoke($reviews)
    {
        $html =
            '<div class="list">';

        $view = $this->getView();

        foreach($reviews as $review) {
            $html .=
                '<div class="item">'
                    .'<div class="text">' . nl2br($review->get('review')) . '</div>'
                    .'<span class="name">' . $review->get('name') .  '</span>, ' . $view->date($review->get('time_create'));

            if($review->get('answer')) {
                $html .=
                    '<div class="answer">'
                        .'<div class="name"><b>Администрация</b></div>'
                        .'<div class="text">'
                            . nl2br($review->get('answer'))
                        .'</div>'
                    .'</div>';
            }

            $html .=
                '</div>';
        }

        $html .=
            '</div>';

        return $html;
    }
}