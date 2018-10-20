<?php
namespace CatalogAdmin\Form;

use Aptero\Form\Admin\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class SuppliesSettingsForm extends Form
{
    public function __construct()
    {
        parent::__construct('settings-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $this->add(array(
            'name' => 'settings-euro',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Курс евро',
            ),
        ));

        $this->add(array(
            'name' => 'settings-dollar',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Курс доллара',
            ),
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'settings-euro',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}