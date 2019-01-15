<?php
namespace Aptero\Form\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Fieldset extends AbstractHelper
{
    public function __invoke($form, $elementsNames, $legend = '')
    {
        $html =
            '<fieldset>';

        if($legend) {
            $html .=
                '<legend>' . $legend . '</legend>';
        }

        foreach($elementsNames as $elementName) {

            $element = $form->get($elementName);
            $options = $element->getOptions();

            $html .=
                '<div class="row">';

            //Label
            if(!empty($options['help'])) {
                $html .=
                    '<span class="label tooltip">'
                        . $element->getLabel()
                        . '<div class="tooltip">'
                            . '<div class="tooltip-icon">?</div>'
                            . '<div class="tooltip-desc">'
                                . $options['help']
                            . '</div>'
                        . '</div>'
                    . '</span>';
            } else {
                $html .=
                    '<span class="label">' . $element->getLabel() . '</span>';
            }

            $html .=
                $this->getView()->formElement($element);

            $html .=
                '</div>';
        }

        $html .=
            '</fieldset>';

        return $html;
    }
}