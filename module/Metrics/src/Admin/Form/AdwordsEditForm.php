<?php
namespace MetricsAdmin\Form;

use Aptero\Form\Form;

use Aptero\String\Date;
use MetricsAdmin\Model\Adwords;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class AdwordsEditForm extends Form
{
    public function __construct()
    {
        parent::__construct('edit-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $this->add([
            'name' => 'id',
            'type'  => 'Zend\Form\Element\Hidden',
        ]);

        $this->add([
            'name' => 'source',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Источник',
            ],
        ]);

        $this->add([
            'name' => 'campaign',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Компания',
            ],
        ]);

        $this->add([
            'name' => 'src_type',
            'type'  => 'Zend\Form\Element\Select',
            'options' => [
                'label' => 'Тип источника',
                'options' => Adwords::$types
            ],
        ]);

        $this->add([
            'name' => 'cost',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Бюджет',
            ],
        ]);

        $this->add([
            'name' => 'month',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                'options' => Date::getMonths(),
                'label' => 'Месяц',
            ],
        ]);

        $this->add([
            'name' => 'year',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                'options' => Date::getYears(),
                'label' => 'Год',
            ],
        ]);
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput([
            'name'     => 'source',
            'required' => true,
            'filters'  => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
        ]));

        $inputFilter->add($factory->createInput([
            'name'     => 'campaign',
            'required' => true,
            'filters'  => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
        ]));

        $this->setInputFilter($inputFilter);
    }
}