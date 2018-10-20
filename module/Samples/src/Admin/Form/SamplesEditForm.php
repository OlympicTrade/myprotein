<?php
namespace SamplesAdmin\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class SamplesEditForm extends Form
{
    public function __construct()
    {
        parent::__construct('edit-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $this->add([
            'name' => 'id',
            'type'  => 'Zend\Form\Element\Hidden',
        ]);

        $this->add([
            'name' => 'name',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Название',
                'help'  => 'Помощь'
            ],
        ]);

        $this->add([
            'name' => 'image-image',
            'type'  => 'Aptero\Form\Element\Admin\Image',
            'options' => [],
        ]);

        $this->add([
            'name' => 'select',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                'options' => [
                    1 => 'Пункт 1',
                    2 => 'Пункт 2',
                    3 => 'Пункт 3'
                ],
                'label' => 'Селект',
            ],
        ]);

        $this->add([
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'radio',
            'options' => [
                'label' => 'Радио кнопки',
                'value_options' => [
                    '1' => 'Активен',
                    '0' => 'Не активен',
                ],
                'help' => 'Помощь'
            ],
            'attributes' => [
                'value' => '1'
            ]
        ]);
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput([
            'name'     => 'name',
            'required' => false,
            'filters'  => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name'    => 'StringLength',
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min'      => 6,
                        'max'      => 30,
                    ],
                ],
                [
                    'name'    => 'Regex',
                    'options' => [
                        'pattern' => '/^[a-zA-Z1-9]*$/'
                    ],
                ],
            ],
        ]));

        $this->setInputFilter($inputFilter);
    }
}