<?php
namespace Catalog\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class OrderStep1Form extends Form
{
    public function setModel($model)
    {
        parent::setModel($model);
    }

    public function __construct()
    {
        parent::__construct('order-form');

        $this->add([
            'name'  => 'oid',
            'type'  => 'Zend\Form\Element\Text',
        ]);

        $this->add([
            'name'  => 'phone',
            'type'  => 'Zend\Form\Element\Text',
        ]);

        $this->add([
            'name'  => 'attrs-name',
            'type'  => 'Zend\Form\Element\Text',
        ]);
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput([
            'name'     => 'attrs-name',
            'required' => true,
            'filters'  => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags'],
            ],
        ]));

        $inputFilter->add($factory->createInput([
            'name'     => 'phone',
            'required' => true,
            'filters'  => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags'],
            ],
        ]));

        $this->setInputFilter($inputFilter);
    }
}