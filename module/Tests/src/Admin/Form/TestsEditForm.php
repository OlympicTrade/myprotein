<?php
namespace TestsAdmin\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class TestsEditForm extends Form
{
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
            'name' => 'image-image',
            'type'  => 'Aptero\Form\Element\Admin\Image',
            'options' => array(),
        ));

        $this->add(array(
            'name' => 'select',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'options' => array(
                    1 => 'Пункт 1',
                    2 => 'Пункт 2',
                    3 => 'Пункт 3'
                ),
                'label' => 'Селект',
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'radio',
            'options' => array(
                'label' => 'Радио кнопки',
                'value_options' => array(
                    '1' => 'Активен',
                    '0' => 'Не активен',
                ),
                'help' => 'Помощь'
            ),
            'attributes' => array(
                'value' => '1'
            )
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'name',
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
                        'min'      => 6,
                        'max'      => 30,
                    ),
                ),
                array(
                    'name'    => 'Regex',
                    'options' => array(
                        'pattern' => '/^[a-zA-Z1-9]*$/'
                    ),
                ),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}