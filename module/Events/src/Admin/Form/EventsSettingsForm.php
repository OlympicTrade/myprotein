<?php
namespace EventsAdmin\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class EventsSettingsForm extends Form
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
            'name' => 'settings-smsru-api_id',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'API ID',
            ),
        ));

        $this->add(array(
            'name' => 'settings-smsru-login',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Логин',
            ),
        ));

        $this->add(array(
            'name' => 'settings-smsru-password',
            'type'  => 'Zend\Form\Element\Password',
            'options' => array(
                'label' => 'Пароль',
            ),
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'settings-smsru-api_id',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'settings-smsru-login',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'settings-smsru-password',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}