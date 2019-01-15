<?php
namespace Aptero\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Video extends AbstractHelper
{
    public function __invoke($url)
    {
        if(!$url) {
            return '';
        }

        return '<iframe class="video-frame" width="560" height="315" src="' . str_replace(array('watch?v='), array('embed/'), $url) . '" frameborder="0" allowfullscreen></iframe>';
    }
}