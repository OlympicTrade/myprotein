<?php
namespace Contacts\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class FeedbackForm extends Form
{
    public function __construct()
    {
        parent::__construct('feedback-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $this->add(array(
            'name' => 'phone'
        ));

        $this->add(array(
            'name' => 'email'
        ));

        $this->add(array(
            'name' => 'name'
        ));

        $this->add(array(
            'name' => 'question',
            'type'  => 'Zend\Form\Element\Textarea'
        ));

    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'email',
            'required' => false,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
                array('name' => 'StringToLower'),
            ),
            'validators' => array(
                array('name'    => 'EmailAddress',),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'name',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
                array('name' => 'StripTags'),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'phone',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
                array('name' => 'StripTags'),
            )
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'question',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
                array('name' => 'StripTags'),
            )
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'text',
            'required' => false,
            'filters'  => array(
                array('name' => 'StringTrim'),
                array('name' => 'StripTags'),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}