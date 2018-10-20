<?php
namespace MainpageAdmin\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class MainpageSettingsForm extends Form
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

        $this->add(array(
            'name' => 'settings-qiwi_shop_id',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'SHOP_ID',
            ),
        ));

        $this->add(array(
            'name' => 'settings-qiwi_shop_id',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'KEY',
            ),
        ));

        $this->add(array(
            'name' => 'settings-qiwi_shop_id',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'PSWD',
            ),
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'settings-title',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}