<?php
namespace ApplicationAdmin\Form;

use Zend\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;


class MenuFilterForm extends Form
{
    public function __construct()
    {
        parent::__construct('filter-form');

        $this->setAttribute('method', 'get');

        $this->add(array(
            'name' => 'search',
            'type'  => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => 'Применить'
            )
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'search',
            'required' => false,
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}