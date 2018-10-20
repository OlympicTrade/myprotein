<?php
namespace CatalogAdmin\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class OrdersSettingsForm extends Form
{
    public function __construct()
    {
        parent::__construct('settings-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $this->add(array(
            'name' => 'settings-bonus',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Бонус (коэфициент)',
                'help'  => '0.01 => 1%'
            ),
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'settings-bonus',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}