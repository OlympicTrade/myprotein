<?php
namespace SliderAdmin\Form;

use ApplicationAdmin\Model\Page;
use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class SliderEditForm extends Form
{
    public function setModel($model)
    {
        parent::setModel($model);

        $this->get('image-image')->setOptions(array(
            'model' => $model->getPlugin('image')
        ));
    }

    public function __construct()
    {
        parent::__construct('edit-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $this->add(array(
            'name' => 'id',
            'type'  => 'Zend\Form\Element\Hidden',
        ));

        $this->add(array(
            'name' => 'title',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Заголовок',
            ),
        ));

        $this->add(array(
            'name' => 'desc',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Описание',
            ),
        ));

        $this->add(array(
            'name' => 'btn',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Текст кнопки',
            ),
        ));

        $this->add(array(
            'name' => 'color',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Цвет текста',
            ),
        ));

        $this->add(array(
            'name' => 'active',
            'type' => 'Zend\Form\Element\Radio',
            'options' => array(
                'options' => array(
                    1 => 'Вкл',
                    0 => 'Выкл',
                ),
                'label' => 'Слайдер активен',
            ),
        ));

        $this->add(array(
            'name' => 'url',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'ссылка (URL)',
            ),
        ));

        $this->add(array(
            'name' => 'image-image',
            'type'  => 'Aptero\Form\Element\Admin\Image',
            'options' => array(),
        ));

        $opts = array();
        for($i = 0; $i < 100; $i++) { $opts[$i] = $i; }

        $this->add(array(
            'name' => 'sort',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'options' => $opts,
                'label' => 'Вес (сортировка)',
                'help'  => 'Чем больше вес, тем дальше элемент в списке меню'
            ),
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $this->setInputFilter($inputFilter);
    }
}