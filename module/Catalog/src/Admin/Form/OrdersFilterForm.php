<?php
namespace CatalogAdmin\Form;

use CatalogAdmin\Model\Orders;
use Zend\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

use User\Model\User;

class OrdersFilterForm extends Form
{
    public function __construct()
    {
        parent::__construct('list-filter');

        $this->setAttribute('method', 'get');

        $this->add([
            'name' => 'search',
        ]);

        $this->add([
            'name' => 'adwords_id',
        ]);

        $this->add([
            'name' => 'status',
            'type'  => 'Zend\Form\Element\Select',
            'options' => [
                'label' => 'Статус',
                'options' => ['' => 'Все заказы'] + Orders::$processStatuses,
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
            'name'     => 'status',
            'required' => false,
        ]));

        $this->setInputFilter($inputFilter);
    }
}