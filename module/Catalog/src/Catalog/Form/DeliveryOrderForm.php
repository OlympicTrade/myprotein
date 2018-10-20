<?php
namespace Catalog\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class DeliveryOrderForm extends Form
{
    public function __construct()
    {
        parent::__construct('order-form');

        $this->add(array(
            'name' => 'attrs-delivery',
            'type' => 'Zend\Form\Element\Radio',
            'options' => array(
                'options' => array(
                    'pickup' => 'pickup',
                    'delivery' => 'delivery',
                ),
            ),
        ));

        $this->add(array(
            'name' => 'attrs-address',
            'type'  => 'Zend\Form\Element\Text',
        ));

        $this->add(array(
            'name' => 'attrs-payment',
            'type' => 'Zend\Form\Element\Radio',
            'options' => array(
                'options' => array(
                    'cash' => 'cash',
                    'bill' => 'bill',
                ),
            ),
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'attrs-delivery',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
                array('name' => 'StripTags '),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'attrs-address',
            'filters'  => array(
                array('name' => 'StringTrim'),
                array('name' => 'StripTags '),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'attrs-payment',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
                array('name' => 'StripTags '),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}