<?php
namespace Aptero\Form\View\Helper\Admin;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\AbstractHelper;

class Images extends AbstractHelper
{
    public function render(ElementInterface $element)
    {
        $model = $element->getOption('model');

        $html =
            '<div class="images-list" data-name="' . $element->getName() . '">'
                .'<div class="list">';

        foreach($model as $image) {

            $html .=
                '<div class="img">'
                    .'<span class="delete" data-id="' . $image->getId() . '">'
                        .'<i class="fa fa-times-circle"></i>'
                    .'</span>'
                    .'<img src="' . $image->getImage('a') . '">'
                .'</div>';
        }

        $html .=
                '</div>'
                .'<div class="row">'
                    .'<input type="button" class="btn btn-green" onclick="showFileManager(this)" value="Обзор">'
                    .' <input type="text" class="new-img">'
                .'</div>'
                .'<div class="row"><div class="btn btn-blue add">Добавить</div></div>'
            .'</div>';

        return $html;
    }

    public function __invoke(ElementInterface $element = null)
    {
        if (!$element) {
            return $this;
        }

        return $this->render($element);
    }
}