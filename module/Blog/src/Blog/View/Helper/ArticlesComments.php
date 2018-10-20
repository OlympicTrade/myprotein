<?php
namespace Blog\View\Helper;

use Application\Model\Menu;
use Application\Model\MenuItems;
use Zend\View\Helper\AbstractHelper;

class ArticlesComments extends AbstractHelper
{
    public function __invoke($article, $comments)
    {
        $view = $this->getView();

        $html = '';

        foreach($comments as $comment) {
            $html .=
                '<div class="item">'
                    .'<div class="header">'
                        .'<span class="name">' . $comment->get('name') . '</span>'
                        .'<span class="date">' . $view->date($comment->get('time_create')/*, array('time' => true)*/) . '</span>'
                        .'<span class="popup answer" href="/blog/add-comment/?aid=' . $article->getId() . '&pid=' . $comment->getId() . '">Ответить</span>'
                    .'</div>'
                    .'<div class="body">' . $comment->get('comment') . '</div>';

            $children = $comment->getChildren();

            if($children->count()) {
                $html .= $this->__invoke($article, $children);
            }

            $html .=
                '</div>';
        }

        return $html;
    }
}