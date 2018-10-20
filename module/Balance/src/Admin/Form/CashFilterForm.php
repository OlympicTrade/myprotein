<?php
namespace BalanceAdmin\Form;

use BalanceAdmin\Model\BalanceFlow;
use Zend\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class CashFilterForm extends Form
{
    public function setData($data)
    {
        $dt = \DateTime::createFromFormat('Y-m-d', date('Y-m-01'));

        if(!isset($data['date_from'])) {
            $data['date_from'] = $dt->format('Y-m-01');
        }

        if(!isset($data['date_to'])) {
            $data['date_to'] = $dt->modify('+1 month')->modify('-1 day')->format('Y-m-d');
        }

        return parent::setData($data);
    }

    public function __construct()
    {
        parent::__construct('UserFilter');

        $this->setAttribute('method', 'get');

        $this->add([
            'name' => 'search',
        ]);

        $this->add([
            'name' => 'type',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                'options' => (['0' => 'Все'] + BalanceFlow::$flowTypes),
                'label'   => 'Тип',
            ],
        ]);

        $this->add([
            'name' => 'date_from',
            'type' => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Дата с',
            ],
            'attributes' => [
                'class' => 'datepicker'
            ]
        ]);

        $this->add([
            'name' => 'date_to',
            'type' => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Дата по',
            ],
            'attributes' => [
                'class' => 'datepicker'
            ]
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
            'name'     => 'type',
            'required' => false,
        ]));

        $inputFilter->add($factory->createInput([
            'name'     => 'date_from',
            'required' => false,
        ]));

        $inputFilter->add($factory->createInput([
            'name'     => 'date_to',
            'required' => false,
        ]));

        $this->setInputFilter($inputFilter);
    }
}