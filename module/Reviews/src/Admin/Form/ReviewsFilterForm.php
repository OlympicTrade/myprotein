<?php
namespace ReviewsAdmin\Form;

use Zend\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

use User\Model\User;

class ReviewsFilterForm extends Form
{
    public function __construct()
    {
        parent::__construct('UserFilter');

        $this->setAttribute('method', 'get');

        $this->add(array(
            'name' => 'search',
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