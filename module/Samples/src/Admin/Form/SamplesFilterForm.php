<?php
namespace SamplesAdmin\Form;

use Zend\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

use User\Model\User;

class SamplesFilterForm extends Form
{
    public function __construct()
    {
        parent::__construct('UserFilter');

        $this->setAttribute('method', 'get');

        $this->add([
            'name' => 'search',
        ]);
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput([
            'name'     => 'search',
            'required' => false,
            'filters'  => [
                ['name' => 'StringTrim'],
            ],
        ]));

        $this->setInputFilter($inputFilter);
    }
}