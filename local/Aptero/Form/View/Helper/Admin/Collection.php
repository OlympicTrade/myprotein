<?php
namespace Aptero\Form\View\Helper\Admin;

use Aptero\Form\Element\TreeSelect;
use Zend\Form\Element\Select;
use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\AbstractHelper;

class Collection extends AbstractHelper
{
    public function render(ElementInterface $element)
    {
        $options = $element->getOption('options');

        $html =
            '<div class="collection-list" data-name="' . $element->getName() . '">'
            .'<table class="std-table list">'
            .'<tr>';

        foreach($options as $field => $opts) {
            if(is_string($opts)) {
                $options[$field] = array();
                $options[$field]['label'] = $opts;
                $options[$field]['width'] = 'auto';
            }
            $html .= '<th style="width: ' . $options[$field]['width'] . 'px">' . $options[$field]['label'] . '</th>';
        };

        $html .=
            '<th style="width: 50px"></th>'
            .'</tr>';

        foreach($element->getOption('model') as $row) {
            $html .=  '<tr>';

            foreach($options as $field => $opts) {
                $name = $element->getName() . '[add][' . $field . '][]';
                $html .= '<td>';
                if($opts['options']) {
                    if(is_array($opts['options'])) {
                        $select = new Select($name, ['options' => $opts['options']]);
                    } else {
                        $select = new TreeSelect($name, ['collection' => $opts['options'], 'sort' => $opts['sort']]);
                    }
                    $select->setAttributes(['data-name' => $field])->setValue($row->get($field));
                    $html .= $this->getView()->formElement($select);
                } else {
                    $html .= '<input type="text" name="' . $name . '" value="' . htmlspecialchars($row->get($field)) . '">';
                }
                $html .= '</td>';
            };

            $html .=
                '<td>'
                .'<input type="hidden" name="' . $element->getName() . '[add][id][]" value="' . $row->getId() . '">'
                .'<span class="btn btn-blue del"><i class="fa fa-trash"></i></span>'
                .'</td>'
                .'</tr>';
        };

        $html .=
            '</table>'
            .'<div class="form">';

        foreach($options as $field => $opts) {
            $html .=
                '<div class="row">'
                .'<span class="label">' . $opts['label'] . '</span>';

            if($opts['options']) {
                if(is_array($opts['options'])) {
                    $select = new Select(' ', ['options' => $opts['options']]);
                } else {
                    $select = new TreeSelect('', ['collection' => $opts['options'], 'sort' => $opts['sort']]);
                }
                $select->setAttributes(['data-name' => $field]);
                $html .= $this->getView()->formElement($select);
            } else {
                $html .= '<input type="text" data-name="' . $field . '" value="">';
            }

            $html .=
                '</div>';
        }

        $html .=
            '<div class="btns"><span class="btn btn-blue add">Добавить</span></div>'
            .'</div>'
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