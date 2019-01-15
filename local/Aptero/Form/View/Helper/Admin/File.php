<?php
namespace Aptero\Form\View\Helper\Admin;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\AbstractHelper;

class File extends AbstractHelper
{
    public function render(ElementInterface $element)
    {
        $element->setAttribute('class', 'fm');

        $model = $element->getOption('model');

        $html =
            '<div class="file-form">'
                . '<div class="info">'
                    . '<div class="row file">'
                        . '<input type="text" name="' . $element->getName() . '[filepath]" placeholder="Файл">'
                        . ' <input type="button" class="btn btn-green" onclick="showFileManager(this)" value="Обзор">'
                    . '</div>'
                . '</div>'
                . ($model->hasFile() ? '<a href="' . $model->getFile() . '" target="_blank" class="left-col">Открыть текущий файл</a> | <span data-name="' . $element->getName() . '[del]" class="del-file">Удалить</span>' : '')
            . '</div>';

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