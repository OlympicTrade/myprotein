<?php
namespace Aptero\Form\View\Helper\Admin;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\AbstractHelper;

class ExercisesImages extends AbstractHelper
{
    public function render(ElementInterface $element)
    {
        $model = $element->getOption('model');

        $html =
            '<div class="images-list" data-name="' . $element->getName() . '">'
                .'<div class="list">';

        foreach($model as $image) {
            $sexOpts = ['m' => 'Муж.', 'w' => 'Жен.'];
            $typeOpts = ['anatomy' => 'Анатомическая карта', 'photo' => 'Пример упражения'];

            $html .=
                '<div class="img">'
                    .'<span class="delete" data-id="' . $image->getId() . '">'
                        .'<i class="fa fa-times-circle"></i>'
                    .'</span>'
                    .'<div class="sort">' . $image->get('sort') . '</div>'
                    .'<img src="' . $image->getImage('a') . '">'
                    .'<div class="prop">' . $sexOpts[$image->get('sex')] . '</div>'
                    .'<div class="prop">' . $typeOpts[$image->get('type')] . '</div>'
                    .'<div class="prop">' . $image->get('desc') . '</div>'
                .'</div>';
        }

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
                        . '<span class="label">Пол:</span> <select data-name="sex"><option value="m">Мужской</option><option value="w">Женский</option></select>'
                    .'</div>'
                    .'<div class="row">'
                        . '<span class="label">Тип картинки:</span> <select data-name="type"><option value="anatomy">Анатомическая карта</option><option value="photo">Пример упражения</option></select>'
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