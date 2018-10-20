<?php
namespace Catalog\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class FastOrderForm extends Form
{
    public function setModel($model)
    {
        parent::setModel($model);
    }

    public function __construct()
    {
        parent::__construct('order-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $this->add(array(
            'name' => 'attrs-phone',
            'type'  => 'Zend\Form\Element\Text',
        ));

        $this->add(array(
            'name' => 'attrs-name',
            'type'  => 'Zend\Form\Element\Text',
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'attrs-phone',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
                array('name' => 'StripTags '),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'attrs-name',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
                array('name' => 'StripTags '),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}