<?php
namespace NewsAdmin\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class NewsEditForm extends Form
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
            'name' => 'name',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Название новости',
            ),
        ));

        $this->add(array(
            'name' => 'preview',
            'type'  => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label' => 'Краткое содержание',
            ),
        ));

        $this->add(array(
            'name' => 'title',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Заголовок (Title)'
            ),
        ));

        $this->add(array(
            'name' => 'keywords',
            'type'  => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label' => 'Ключевые слова (Keywords)'
            ),
        ));

        $this->add(array(
            'name' => 'description',
            'type'  => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label' => 'Описание (Description)'
            ),
        ));

        $this->add(array(
            'name' => 'text',
            'type'  => 'Zend\Form\Element\Textarea',
            'attributes'=>array(
                'class' => 'editor',
                'id'    => 'page-text'
            ),
        ));

        $this->add(array(
            'name' => 'author',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Автор',
            ),
        ));

        $this->add(array(
            'name' => 'date',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Дата новости',
            ),
            'attributes'=>array(
                'class' => 'datepicker',
            ),
        ));

        $this->add(array(
            'name' => 'status',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'options' => array(
                    1 => 'Да',
                    0 => 'Нет',
                ),
                'label' => 'Показать на сайте',
            ),
        ));

        $this->add(array(
            'name' => 'url',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Url',
                'help'  => 'Используется как ЧПУ. По умолчанию заполняется транслитом на основании названия страницы'
            ),
        ));

        $this->add(array(
            'name' => 'image-image',
            'type'  => 'Aptero\Form\Element\Admin\Image',
            'options' => array(),
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'name',
            'required' => true,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'preview',
            'required' => false,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}