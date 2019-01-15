<?php
namespace Aptero\Form\View\Helper\Admin;

use Aptero\Form\Element\TreeSelect;
use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\AbstractHelper;

class ProductImages extends AbstractHelper
{
    public function render(ElementInterface $element)
    {
        $model = $element->getOption('model');

        $html =
            '<div class="images-list" data-name="' . $element->getName() . '">'
                .'<div class="list">';

        foreach($model as $image) {

            $html .=
                '<div class="img">'
                    .'<span class="delete" data-id="' . $image->getId() . '">'
                        .'<i class="fa fa-times-circle"></i>'
                    .'</span>'
                    .'<div class="sort">' . $image->get('sort') . '</div>'
                    .'<img src="' . $image->getImage('a') . '">'
                    .'<div class="prop">' . $image->get('desc') . '</div>'
                    .'<div class="prop">' . $image->getTaste()->get('name') . '</div>'
                    .'<div class="prop">' . $image->getSize()->get('name') . '</div>'
                .'</div>';
        }

        $tasteSelect = new TreeSelect('', [
            'collection' => $element->getOption('product')->getPlugin('taste'),
            'empty'      => 'Без привязки'
        ]);
        $tasteSelect->setAttribute('data-name', 'taste_id');

        $sizeSelect = new TreeSelect('', [
            'collection' => $element->getOption('product')->getPlugin('size'),
            'empty'      => 'Без привязки'
        ]);
        $sizeSelect->setAttribute('data-name', 'size_id');

        $html .=
                '</div>'
                .'<div class="form">'
                    .'<div class="row">'
                        .'<input type="button" class="btn btn-green" onclick="showFileManager(this)" value="Обзор">'
                        .' <input type="text" data-name="path">'
                    .'</div>'
                    .'<div class="row">'
                        . '<span class="label">Сортировка:</span> <input type="text" data-name="sort" placeholder="0 - самый высокий приоритет">'
                    .'</div>'
                    .'<div class="row">'
                        . '<span class="label">Описание:</span> <input type="text" data-name="desc">'
                    .'</div>'
                    .'<div class="row">'
                        . '<span class="label">Вкус/Цвет:</span> ' . $this->getView()->formElement($tasteSelect)
                    .'</div>'
                    .'<div class="row">'
                        . '<span class="label">Размер:</span> ' . $this->getView()->formElement($sizeSelect)
                    .'</div>'
                    .'<div class="row"><div class="btn btn-blue add">Добавить</div></div>'
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