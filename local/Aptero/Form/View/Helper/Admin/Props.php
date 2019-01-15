<?php
namespace Aptero\Form\View\Helper\Admin;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\AbstractHelper;

class Props extends AbstractHelper
{
    public function render(ElementInterface $element)
    {
        $model = $element->getOption('model');

        $html =
            '<div class="props-list" data-name="' . $element->getName() . '">'
                .'<div class="list">';

        foreach($model as $props) {
            $html .=
                '<div class="row">'
                    .'<input type="text" name="' . $element->getName() . '[edit-' . $props['id'] . ']" value="' . $props['value'] . '" disabled>'
                    .' <div class="btn btn-red remove" data-id="' . $props['id'] . '"><i class="fa fa-trash-o"></i></div>'
                    .' <div class="btn btn-blue edit" data-id="' . $props['id'] . '"><i class="fa fa-edit"></i></div>'
                .'</div>';
        }

        $html .=
                '</div>'
                .'<div class="row"><div class="btn btn-blue add">Добавить параметр</div></div>'
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