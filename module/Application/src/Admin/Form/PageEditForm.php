<?php
namespace ApplicationAdmin\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class PageEditForm extends Form
{
    public function setModel($model)
    {
        parent::setModel($model);

        $this->get('parent')->setOption('model', $this->getModel());

        if($model->getPlugin('module')->getId()) {
            $this->get('url')->setAttribute('disabled', true);
            $this->get('parent')->setAttribute('disabled', true);
        }

        $this->get('image-image')->setOptions(array(
            'model' => $model->getPlugin('image')
        ));

        //if(!$model->get('layout')) {
            $this->get('layout')->setValue(1);
        //}
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
            'name' => 'image-image',
            'type'  => 'Aptero\Form\Element\Admin\Image',
            'options' => array(),
        ));

        $this->add(array(
            'name' => 'name',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label'     => 'Название страницы',
                'required'  => true
            ),
        ));

        $this->add(array(
            'name' => 'header',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Заголовок страницы',
                'help'  => 'Используется как заголовок (H1) на странице сайта'
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
            'name' => 'redirect_url',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Редирект (Url)',
                'help'  => '301 редирект с адреса, указанного в Url'
            ),
        ));

        $this->add(array(
            'name' => 'parent',
            'type'  => 'Aptero\Form\Element\TreeSelect',
            'options' => array(
                'label'   => 'Родительский елемент',
                'empty'   => '',
            ),
        ));

        $this->add(array(
            'name' => 'layout',
            'type' => 'Zend\Form\Element\Radio',
            'options' => array(
                'options' => array(
                    1 => 'Модуль',
                    3 => 'Статья',
                ),
                'label' => 'Шаблон',
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
            'name' => 'alias',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Переадресация на другую страницу или сайт'
            )
        ));

        $this->add(array(
            'name' => 'text',
            'type'  => 'Zend\Form\Element\Textarea',
            'attributes'=>array(
                'class' => 'editor',
                'id'    => 'page-text'
            ),
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
                array('name' => 'StringTrim'),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'url',
            'required' => false,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            )
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'redirect_url',
            'required' => false,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            )
        )));

        $this->setInputFilter($inputFilter);
    }
}