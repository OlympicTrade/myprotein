<?php
namespace Aptero\Form\View\Helper;

use Zend\I18n\View\Helper\AbstractTranslatorHelper;
use Zend\Form\ElementInterface;

class FormErrors extends AbstractTranslatorHelper
{
    public function __invoke($errors)
    {
        if(empty($errors)) {
            return '';
        }

        $html =
            '<div class="msgs">';

        foreach($errors as $error) {
            $html .= '<div class="error">' . $error . '</div>';
        }

        $html .=
            '</div>';

        return $html;
    }
}