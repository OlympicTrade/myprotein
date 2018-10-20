<?php
namespace Application\View\Helper;

use Application\Model\Menu;
use Application\Model\MenuItems;
use Zend\View\Helper\AbstractHelper;

class TextBlock extends AbstractHelper
{
    public function __invoke($text)
    {
        if($text) {
            return '<div class="std-text">' . $text . '</div>';
        }
        return '';
    }
}