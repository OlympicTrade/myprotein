<?php
namespace Aptero\Form\View\Helper\Admin;

use Zend\I18n\View\Helper\AbstractTranslatorHelper;
use Zend\Form\ElementInterface;

class FormRow extends AbstractTranslatorHelper
{
    public function __invoke(ElementInterface $element = null)
    {
        if (!$element) {
            return $this;
        }

        $translator = $this->getTranslator();

        $html =
            '<div class="row">';

        $label = $element->getLabel() ? $translator->translate($element->getLabel(), $this->getTranslatorTextDomain()) : '';

        $options = $element->getOptions();

        if(!empty($options['required'])) {
            $label .= ' <span class="asterisk">*</span>';
        }

        $html .=
            '<span class="label">' . $translator->translate($label, $this->getTranslatorTextDomain()) . '</span>';

        $html .=
            $this->getView()->formElement($element);

        if(!empty($options['help'])) {
            $html .=
                '<span class="tooltip">'
                    . '<div class="tooltip">'
                    . '<div class="tooltip-icon"><i class="fa fa-question-circle"></i></div>'
                        . '<div class="tooltip-desc">'
                        . $options['help']
                        . '</div>'
                    . '</div>'
                . '</span>';
        }

        $html .=
            '</div>';

        return $html;
    }
}