<?php
namespace MetricsAdmin\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class AdwordsSettingsForm extends Form
{
    public function setModel($model)
    {
        parent::setModel($model);
    }

    public function __construct()
    {
        parent::__construct('settings-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $this->add([
            'name' => 'settings-qiwi_shop_id',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'SHOP_ID',
            ],
        ]);

        $this->add([
            'name' => 'settings-qiwi_shop_id',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'KEY',
            ],
        ]);

        $this->add([
            'name' => 'settings-qiwi_shop_id',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'PSWD',
            ],
        ]);
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput([
            'name'     => 'settings-title',
            'required' => true,
            'filters'  => [
                ['name' => 'StringTrim'],
            ],
        ]));

        $this->setInputFilter($inputFilter);
    }
}