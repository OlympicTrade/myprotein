<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Header extends AbstractHelper
{
    public function __invoke($options = array())
    {
        if($this->getView()->isMobile()) {
            return $this->mobile($options);
        } else {
            return $this->desktop($options);
        }
    }

    public function mobile($options = array())
    {
        $view = $this->getView();

        if(!isset($options['header'])) {
            $options['header'] = $view->header;
        }

        if(!isset($options['blink'])) {
            $options['blink'] = 'javascript:history.back()';
        }

        $html =
            '<a class="block-header" href="' . $options['blink'] . '">'
                .'<div class="back"></div>'
                .'<h1>' . $options['header'] . '</h1>'
            .'</a>';

        return $html;
    }

    public function desktop($options = array())
    {
        $view = $this->getView();

        if(!isset($options['header'])) {
            $options['header'] = $view->header;
        }

        if(!isset($options['breadcrumbs'])) {
            $options['breadcrumbs'] = $view->breadcrumbs;
        }

        $html =
            '<div class="block header-box">'
                .'<div class="wrapper">'
                    . $view->breadcrumbs($options['breadcrumbs'], array('delimiter' => ' — ', 'showLast' => false))
                    .'<h1>' . $options['header'] . '</h1>'
                    .(isset($options['html']) ? $options['html'] : '')
                .'</div>'
            .'</div>';

        return $html;
    }
}