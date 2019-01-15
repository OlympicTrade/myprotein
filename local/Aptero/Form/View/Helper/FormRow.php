<?php
namespace Aptero\Form\View\Helper;

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
            '<span class="label">' . $translator->translate($label, $this->getTranslatorTextDomain());

        if(!empty($options['help'])) {
            $html .=
                '<div class="tooltip help">'
                    . '<div class="tooltip">'
                        . '<div class="tooltip-icon"><i class="fa fa-question-circle"></i></div>'
                        . '<div class="tooltip-desc">'
                            . $options['help']
                        . '</div>'
                    . '</div>'
                . '</div>';
        }

        $html .=
            '</span>';

        $html .=
            $this->getView()->formElement($element);

        $messages = $element->getMessages();
        if($messages) {
            $html .= '<div class="msgs">';
            foreach($messages as $message) {
                $html .= '<div class="error">' . $message . '</div>';
            }
            $html .= '</div>';
        }

        $html .=
            '</div>';

        return $html;
    }
}