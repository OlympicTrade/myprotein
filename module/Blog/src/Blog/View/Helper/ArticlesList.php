<?php
namespace Blog\View\Helper;

use Application\Model\Menu;
use Application\Model\MenuItems;
use Zend\View\Helper\AbstractHelper;

class ArticlesList extends AbstractHelper
{
    public function __invoke($articles)
    {
        $html = '<div class="cols">';

        $view = $this->getView();

        foreach($articles as $article) {
            $url = '/blog/' . $article->get('url') . '/';

            $html .=
                '<div class="article col-33">'
                    .'<a href="' . $url . '" class="pic">'
                        .'<img src="' . $article->getPlugin('image')->getImage('s') . '" alt="' . $article->get('name') . '">'
                    .'</a>'
                    .'<div class="info">'
                        .'<a href="' . $url . '" class="title">' . $article->get('name') . '</a>'
                        .'<div class="desc">' . $view->subStr($article->get('preview'), 200) . '</div>'
                        .'<a href="' . $url . '" class="btn">Читать далее</a>'
                        .'<div class="date">' . $view->date($article->get('time_create')) . '</div>'
                    .'</div>'
                .'</div>';
        }

        $html .= '</div>';

        return $html;
    }
}