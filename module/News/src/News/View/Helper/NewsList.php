<?php
namespace News\View\Helper;

use News\Model\News;
use Zend\Paginator\Paginator;
use Zend\View\Helper\AbstractHelper;

class NewsList extends AbstractHelper
{
    public function __invoke($newsList = null)
    {
        if($newsList === null) {
            $newsList = News::getEntityCollection();
            $newsList->select()->order('id DESC')->limit(3);
        }
        
        if(!$newsList->count()) {
            return '<div class="empty-list">Новостей не найдено</div>';
        }

        $html = '<div class="news-list">';

        $view = $this->getView();

        foreach($newsList as $news) {
            $url = '/news/' . $news->get('url') . '/';

			$hasImg = $news->getPlugin('image')->hasImage();
			
            $html .=
                '<div class="item' . ($hasImg ? '' : ' no-img') . '">';
				
			if($hasImg) {
				$html .=
					'<a href="' . $url . '" class="pic"><img src="' . $news->getPlugin('image')->getImage('s') . '" alt="' . $news->get('name') . '"></a>';
			}
			
			$html .=
                    '<div class="info">'
                        .'<a href="' . $url . '" class="title">' . $news->get('name') . '</a>'
                        .'<div class="desc">' . $view->subStr($news->get('preview'), 360) . '</div>'
                            .'<div class="date"><i class="fas fa-calendar"></i> ' . $view->date($news->get('date')) . '</div>'
                            .'<a href="' . $url . '" class="read">Подробнее</a>'
                    .'</div>'
                    .'<div class="clear"></div>'
                .'</div>';
        }

        $html .= '</div>';

        if($newsList instanceof Paginator) {
            $html .=
                $view->paginationControl($newsList, 'Sliding', 'pagination-slide', array('route' => 'application/pagination'));
        }

        return $html;
    }
}