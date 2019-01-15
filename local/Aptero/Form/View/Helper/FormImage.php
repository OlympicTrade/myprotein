<?php
namespace Aptero\Form\View\Helper;

use Zend\Form\ElementInterface;
use Zend\Form\Exception;
use Zend\Form\View\Helper\FormInput;

class FormImage extends FormInput
{
    /**
     * Attributes valid for the input tag type="image"
     *
     * @var array
     */
    protected $validTagAttributes = array(
        'name'           => true,
        'alt'            => true,
        'autofocus'      => true,
        'disabled'       => true,
        'form'           => true,
        'formaction'     => true,
        'formenctype'    => true,
        'formmethod'     => true,
        'formnovalidate' => true,
        'formtarget'     => true,
        'height'         => true,
        'src'            => true,
        'type'           => true,
        'width'          => true,
        'prev-box'       => true,
    );

    public function render(ElementInterface $element)
    {
        if(!$element->getOption('preview')) {
            return parent::render($element);
        }

        $prevId = 'image-prev-' . $element->getName();
        $element->setAttribute('prev-box', $prevId);

        $html =
            '<div class="input-box">'
                .'<div>' . parent::render($element) . '</div>'
                .'<div class="image-prev" id="' . $prevId . '" style="background-image: url(' . $element->getValue() . ')">'
                    .'<div class="del-photo"></div>'
                .'</div>'
            .'</div>';

        return $html;
    }

    protected function getType(ElementInterface $element)
    {
        return 'file';
    }
}
