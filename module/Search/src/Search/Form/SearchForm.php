<?php
namespace Search\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class SearchForm extends Form
{
    public function __construct()
    {
        parent::__construct('search-form');
        $this->setAttribute('method', 'get');

        $this->add(array(
            'name' => 'query',
            'type'  => 'Zend\Form\Element\Text',
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'query',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
                array('name' => 'StripTags '),
            ),
        )));

        $this->setInputFilter($inputFilter);
        return $this;
    }
}