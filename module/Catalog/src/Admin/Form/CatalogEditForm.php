<?php
namespace CatalogAdmin\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class CatalogEditForm extends Form
{
    public function setModel($model)
    {
        parent::setModel($model);

        $this->get('parent')->setOptions(array(
            'model' => $this->getModel(),
            'sort'  => 'name',
            'empty' => ''
        ));

        $this->get('image-image')->setOptions(array(
            'model' => $model->getPlugin('image'),
        ));

        /*$this->get('props-props')->setOptions(array(
            'model' => $model->getPlugin('props'),
        ));*/
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
                'label' => 'Название',
                'help'  => 'Помощь'
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
            'name' => 'parent',
            'type'  => 'Aptero\Form\Element\TreeSelect',
            'options' => array(
                'label'   => 'Родительский каталог',
            ),
        ));

        $this->add(array(
            'name'  => 'ya_market_id',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label'   => 'Яндекс Маркет ID',
            ),
        ));

        $this->add(array(
            'name' => 'header',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Заголовок страницы (H1)'
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
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Ключевые слова (Keywords)'
            ),
        ));

        $this->add(array(
            'name' => 'description',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Описание (Description)'
            ),
        ));

        $this->add(array(
            'name' => 'image-image',
            'type'  => 'Aptero\Form\Element\Admin\Image',
            'options' => array(),
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
            'name' => 'active',
            'type' => 'Zend\Form\Element\Radio',
            'options' => array(
                'options' => array(
                    1 => 'Показать',
                    0 => 'Скрыть',
                ),
                'label' => 'Показать на сайте',
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
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            )
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'url',
            'required' => false,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name'    => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min'      => 0,
                        'max'      => 30,
                    ),
                ),
                array(
                    'name'    => 'Regex',
                    'options' => array(
                        'pattern' => '/^[a-zA-Z1-9_-]*$/',
                    ),
                ),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}