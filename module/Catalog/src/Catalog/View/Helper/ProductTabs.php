<?php
namespace Catalog\View\Helper;

use Aptero\File\File;
use Aptero\String\Numbers;
use Catalog\Model\Reviews;
use Zend\Paginator\Paginator;
use Zend\View\Helper\AbstractHelper;

class ProductTabs extends AbstractHelper
{
    public function __invoke($product, $tab)
    {
        $html = '';

        switch($tab) {
            case 'default':
                $html .= $this->renderDesc($product);
                break;
            case 'video':
                $html .= $this->renderVideo($product);
                break;
            case 'articles':
                $html .= $this->renderArticles($product);
                break;
            case 'props':
                $html .= $this->renderProps($product);
                break;
            case 'reviews':
                $html .= $this->renderReviews($product);
                break;
            case 'tab1':
                $html .= $this->renderTab($product, $tab . '_');
                break;
            case 'tab2':
                $html .= $this->renderTab($product, $tab . '_');
                break;
            case 'tab3':
                $html .= $this->renderTab($product, $tab . '_');
                break;
            case 'certificate':
                $html .= $this->renderFile($product, 'certificate');
                break;
            case 'instruction':
                $html .= $this->renderFile($product, 'instruction');
                break;
            default:
        }

        return $html;
    }

    protected function renderFile($product, $pluginName)
    {
        if(!$product->getPlugin($pluginName)->hasFile()) {
            return '';
        }

        $file = $product->getPlugin($pluginName)->getFile();
        $extension = File::getFileType($file);

        if(in_array($extension, array('.jpg', '.png', '.jpeg', '.gif'))) {
            return '<img src="' . $file . '" alt="">';
        }

        if($extension == '.pdf') {
            return '<iframe src="' . $file . '" style="width: 100%; height: 800px"></iframe>';
        }

        return '<a href="' . $file . '" download>Скачать файл</iframe>';
    }

    protected function renderReviews($product)
    {
        $url = '/catalog/add-review/?pid=' . $product->getId();

        $reviews = $product->getPlugin('reviews');

        if(!$reviews->count()) {
            return
                '<div class="reviews-list">'
                .'<div class="empty">Здесь еще никто не оставлял отзывы. Вы можете быть первым. <a class="add-comment popup" href="' . $url . '">Оставить отзыв</a></div>'
                .'</div>';
        }

        $view = $this->getView();

        $html =
            '<div class="reviews-list">'
                .'<div class="summary">'
                    .'<span class="btn add-comment popup" href="' . $url . '">Оставить отзыв</span>'
                    .'<div class="rating">'
                        .'<span>Средняя оценка:</span>'
                        . $view->stars($product->get('stars'))
                    .'</div>'
                .'</div>'
                .'<div class="list">';

        $view = $this->getView();

        foreach($product->getPlugin('reviews') as $review) {
            $html .=
                '<div class="review">'
                    .'<div class="info">'
                        .'<div class="name">' . $review->get('name') .  '</div>'
                        .'<div class="date">' . $view->date($review->get('time_create')) . '</div>'
                        . $view->stars($review->get('stars'))
                    .'</div>'
                    .'<div class="text">'
                        . nl2br($review->get('review'))
                        //.($review->get('source') == Reviews::SOURCE_MYRPOTEIN ? '<div class="source"><a href="' . $product->get('mp_url') . '" target="_blank">отзыв с myprotein</a></div>' : '')
                    . '</div>';

            if($review->get('answer')) {
                $html .=
                    '<div class="answer">'
                        .'<div class="name"><b>Myprotein</b></div>'
                        .'<div class="text">'
                            .nl2br($review->get('answer'))
                        .'</div>'
                    .'</div>';
            }

            $html .=
                '</div>';
        }

        $html .=
                '</div>'
            .'</div>';

        return $html;
    }

    protected function renderTab($product, $tab)
    {
        return $this->getView()->textBlock($product->get($tab . 'text'));
    }

    protected function renderDesc($product)
    {
        $html =
            '<div class="col-l std-text">'
                .$product->get('text')
            .'</div>'
            .'<div class="col-r">';

        if($product->getPlugin('composition')->count()) {
            $html .=
                '<div class="composition">';
            foreach($product->getPlugin('composition') as $row) {
				switch($row->get('type')) {
					case 1:
						$html .=
							'<div class="title">'
								. $row->get('name') . '<div class="val">' . $row->get('percent') . '</div>'
							.'</div>';
						break;
					case 3:
						$html .=
							'<div class="row sub">'
								. $row->get('name') . '<div class="val">' . $row->get('percent') . '</div>'
							.'</div>';
						break;
					default:
						$html .=
							'<div class="row">'
								. $row->get('name') . '<div class="val">' . $row->get('percent') . '</div>'
							.'</div>';
				}
            }
            $html .=
                '</div>';
        }
        $html .=
            '</div>';

        return $html;
    }

    protected function renderVideo($product)
    {
        return $this->getView()->video($product->get('video'));
    }

    protected function renderArticles($product)
    {
        $articles = $product->getPlugin('articles');

        if(!$articles->count()) { return ''; }

        $html = '<div class="products-articles">';

        $view = $this->getView();
        foreach($articles as $article) {
            $url = '/blog/' . $article->get('url') . '/';

            $html .=
                '<div class="article">'
                    .'<a href="' . $url . '" class="pic">'
                        .'<img src="' . $article->getPlugin('image')->getImage('s') . '" alt="' . $article->get('name') . '">'
                    .'</a>'
                    .'<div class="info">'
                        .'<a href="' . $url . '" class="title">' . $article->get('name') . '</a>'
                        .'<div class="desc">' . $view->subStr($article->get('preview'), 250) . '</div>'
                        .'<a href="' . $url . '" class="read">Читать далее</a>'
                    .'</div>'
                    .'<div class="clear"></div>'
                .'</div>';
        }

        $html .= '</div>';

        return $html;
    }

    protected function renderProps($product)
    {
        $props = $product->getPlugin('props');

        if(!$props->count()) {
            return '';
        }

        $html =
            '<div class="props">'
                .'<div class="title">Характеристики</div>'
                .'<div class="cols">';

        foreach($props as $prop) {
            $html .=
                '<div class="prop">'
                    .'<span class="label">' . $prop->get('key') . ':</span> '
                    . $prop->get('val')
                .'</div>';
        }

        $html .=
                '</div>'
            .'</div>';

        return $html;
    }

}