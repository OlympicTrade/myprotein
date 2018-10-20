<?php
namespace Application\View\Helper;

use Application\Model\Menu;
use Application\Model\MenuItems;
use Zend\View\Helper\AbstractHelper;

class ContentRender extends AbstractHelper
{
    public function __invoke($blocks)
    {
        $html = '<div class="content-block">';

        foreach($blocks as $block) {
            switch($block->get('type')) {
                case 1:
                    $html .= $this->blockTitle($block);
                    break;
                case 2:
                    $html .= $this->blockText($block);
                    break;
                case 3:
                    $html .= $this->blockImage($block);
                    break;
                default:
                    break;
            }
        }

        $html .= '</div>';

        return $html;
    }

    protected function blockTitle($block) {
        $html =
            '<h2 class="cb-title">' . $block->get('title') . '</h2>';

        return $html;
    }

    protected function blockImage($block) {
        $imgSize = $this->getView()->isMobile() ? 's' : 'm';

        $html =
            '<div class="cb-image">'
                .'<img src="' . $block->getPlugin('image')->getImage($imgSize) . '" alt="' . $block->get('title') . '">'
            .'</div>';

        return $html;
    }

    protected function blockText($block) {
        $html = '';

        if($block->get('title')) {
            $html .= '<h2 class="cb-title">' . $block->get('title') . '</h2>';
        }

        $html .=
            '<div class="cb-text std-text">'
                . $block->get('text')
            . '</div>';

        return $html;
    }
}