<?php
namespace CatalogAdmin\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Form\ElementInterface;

class ProductProps extends AbstractHelper
{
    public function render(ElementInterface $element)
    {
        $model = $element->getOption('model');

        $html =
            '<div class="props-list" data-name="' . $element->getName() . '">'
            .'<div class="list">';

        foreach($model as $key => $props) {
            $html .=
                '<div class="row">'
                .'<div style="float:left;width: 100px;font-size: 11pt; line">' . $key . '</div>'
                .'<input type="text" name="' . $element->getName() . '[' . $key . ']" value="' . $props['value'] . '">'
                .'</div>';
        }

        $html .=
            '</div>'
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