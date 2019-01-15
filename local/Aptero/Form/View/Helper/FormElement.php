<?php
namespace Aptero\Form\View\Helper;

use Aptero\Form\Element;
use Zend\Form\View\Helper\FormElement as ZendFormElement;
use Zend\Form\ElementInterface;

class FormElement extends ZendFormElement
{
    public function render(ElementInterface $element)
    {
        $renderer = $this->getView();
        if (!method_exists($renderer, 'plugin')) {
            return '';
        }

        if ($element instanceof Element\FileManager) {
            $helper = $renderer->plugin('AdminFormFileManager');
            return $helper($element);
        }

        if ($element instanceof Element\TreeSelect) {
            $helper = $renderer->plugin('AdminFormTreeSelect');
            return $helper($element);
        }

        if ($element instanceof Element\Admin\Image) {
            $helper = $renderer->plugin('AdminFormImage');
            return $helper($element);
        }

        if ($element instanceof Element\Admin\Collection) {
            $helper = $renderer->plugin('AdminFormCollection');
            return $helper($element);
        }

        if ($element instanceof Element\Admin\File) {
            $helper = $renderer->plugin('AdminFormFile');
            return $helper($element);
        }

        if ($element instanceof Element\Admin\Props) {
            $helper = $renderer->plugin('AdminFormProps');
            return $helper($element);
        }

        if ($element instanceof Element\Admin\Images) {
            $helper = $renderer->plugin('AdminFormImages');
            return $helper($element);
        }

        if ($element instanceof Element\Admin\ProductImages) {
            $helper = $renderer->plugin('AdminFormProductImages');
            return $helper($element);
        }

        if ($element instanceof Element\Admin\ContentImages) {
            $helper = $renderer->plugin('AdminFormContentImages');
            return $helper($element);
        }

        if ($element instanceof Element\Admin\ExercisesImages) {
            $helper = $renderer->plugin('AdminFormExercisesImages');
            return $helper($element);
        }

        if ($element instanceof Element\Admin\Attrs) {
            $helper = $renderer->plugin('AdminFormAttrs');
            return $helper($element);
        }

        /* Modules */

        //Catalog
        if ($element instanceof \CatalogAdmin\Form\Element\ProductProps) {
            $helper = $renderer->plugin('AdminFormProductProps');
            return $helper($element);
        }

        return parent::render($element);
    }
}