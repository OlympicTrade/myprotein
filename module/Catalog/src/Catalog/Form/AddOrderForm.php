<?php
namespace Catalog\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class AddOrderForm extends Form
{
    public function setModel($model)
    {
        parent::setModel($model);
    }

    public function __construct()
    {
        parent::__construct('order-form');

        $this->add(array(
            'name'  => 'attrs-phone',
            'type'  => 'Zend\Form\Element\Text',
        ));

        $this->add(array(
            'name'  => 'attrs-name',
            'type'  => 'Zend\Form\Element\Text',
        ));

        $this->add(array(
            'name'  => 'attrs-delivery',
            'type'  => 'Zend\Form\Element\Text',
        ));

        $this->add(array(
            'name'  => 'attrs-address',
            'type'  => 'Zend\Form\Element\Text',
        ));

        $this->add(array(
            'name'  => 'attrs-date',
            'type'  => 'Zend\Form\Element\Text',
        ));

        $this->add(array(
            'name'  => 'attrs-point',
            'type'  => 'Zend\Form\Element\Text',
        ));

        $this->add(array(
            'name' => 'attrs-time_from',
            'type'  => 'Zend\Form\Element\Text',
        ));

        $this->add(array(
            'name' => 'attrs-time_to',
            'type'  => 'Zend\Form\Element\Text',
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
                array('name' => 'StripTags'),
            ),
            'validators' => array(
                array(
                    'name' =>'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\NotEmpty::IS_EMPTY => 'Выберите способ доставки'
                        ),
                    ),
                ),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'attrs-phone',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
                array('name' => 'StripTags'),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'attrs-name',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
                array('name' => 'StripTags'),
            ),
        )));


        switch($this->get('attrs-delivery')->getValue()) {
            case 'courier':
                $inputFilter->add($factory->createInput(array(
                    'name'     => 'attrs-address',
                    'required' => true,
                    'filters'  => array(
                        array('name' => 'StringTrim'),
                        array('name' => 'StripTags'),
                    ),
                )));

                $inputFilter->add($factory->createInput(array(
                    'name'     => 'attrs-date',
                    'required' => true,
                    'filters'  => array(
                        array('name' => 'StringTrim'),
                        array('name' => 'StripTags'),
                    ),
                )));

                break;
            case 'pickup':
                $inputFilter->add($factory->createInput(array(
                    'name'     => 'attrs-point',
                    'required' => true,
                    'filters'  => array(
                        array('name' => 'StringTrim'),
                        array('name' => 'StripTags'),
                    ),
                )));

                break;
            default:
        }

        $this->setInputFilter($inputFilter);
    }
}