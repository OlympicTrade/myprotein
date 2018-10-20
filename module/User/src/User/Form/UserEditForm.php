<?php
namespace User\Form;

use Aptero\Form\Form;

use User\Service\AuthService;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class UserEditForm extends Form
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

        $this->add(array(
            'name' => 'attrs-inn',
        ));

        $this->add(array(
            'name' => 'attrs-kpp',
        ));

        $this->add(array(
            'name' => 'attrs-ogrn',
        ));

        $this->add(array(
            'name' => 'attrs-okved',
        ));

        $this->add(array(
            'name' => 'attrs-u_address',
        ));

        $this->add(array(
            'name' => 'attrs-f_address',
        ));

        $this->add(array(
            'name' => 'attrs-c_phone',
        ));

        $this->add(array(
            'name' => 'attrs-bank',
        ));

        $this->add(array(
            'name' => 'attrs-rc',
        ));

        $this->add(array(
            'name' => 'attrs-kc',
        ));

        $this->add(array(
            'name' => 'attrs-bik',
        ));
    }

    public function setFilters($options)
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        foreach($this->getElements() as $element) {
            $inputFilter->add($factory->createInput(array(
                'name'     => $element->getName(),
                'required' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
            )));
        }

        $this->setInputFilter($inputFilter);
    }
}