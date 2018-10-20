<?php
namespace CallcenterAdmin\Form;

use CallcenterAdmin\Model\Call;
use Zend\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

use User\Model\User;

class CallcenterFilterForm extends Form
{
    public function __construct()
    {
        parent::__construct('list-filter');

        $this->setAttribute('method', 'get');

        $this->add([
            'name' => 'search',
        ]);

        $this->add([
            'name' => 'type_id',
            'type'  => 'Zend\Form\Element\Select',
            'options' => [
                'label' => 'Тип заявки',
                'options' => ['' => 'Все типы'] + Call::$types,
            ],
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

        $inputFilter->add($factory->createInput([
            'name'     => 'type_id',
            'required' => false,
        ]));

        $this->setInputFilter($inputFilter);
    }
}