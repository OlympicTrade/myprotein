<?php
namespace Aptero\Form\View\Helper\Admin;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\AbstractHelper;

class Attrs extends AbstractHelper
{
    public function render(ElementInterface $element)
    {
        $model = $element->getOption('model');

        $html =
            '<div class="attrs-list" data-name="' . $element->getName() . '">'
                .'<div class="list">';

        foreach($model as $key => $value) {
            $html .=
                '<div class="row">'
                    .'<input type="text" name="' . $element->getName() . '[keys][]" value="' . $key . '" placeholder="Свойство"> '
                    .'<input type="text" name="' . $element->getName() . '[vals][]" value="' . $value . '" placeholder="Значение" data-filed="val">'
                    .' <div class="btn btn-blue remove" data-id="' . $key . '"><i class="fa fa-trash-o"></i></div>'
                .'</div>';
        }

        $html .=
                '</div>'
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