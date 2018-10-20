<?php
namespace Reviews\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class ReviewForm extends Form
{
    public function __construct()
    {
        parent::__construct('feedback-form');

        $this->add(array(
            'name' => 'name'
        ));

        $this->add(array(
            'name' => 'review'
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
                array('name' => 'StripTags'),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'review',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
                array('name' => 'StripTags'),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}