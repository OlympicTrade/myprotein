<?php
namespace User\Form;

use Aptero\Form\Form;

use User\Service\AuthService;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class UserSubscribeForm extends Form
{
    public function __construct()
    {
        parent::__construct();
        $this->add(array(
            'name' => 'attrs-name',
        ));

        $this->add(array(
            'name' => 'attrs-city',
        ));

        $this->add(array(
            'name' => 'attrs-phone',
        ));

        $this->add(array(
            'name' => 'attrs-company',
        ));
    }

    public function setFilters($options)
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'password_repeat',
            'required' => true,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}