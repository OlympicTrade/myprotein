<?php
namespace ContactsAdmin\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class FeedbackSettingsForm extends Form
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
            'name' => 'settings-email',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'E-mail администратора',
                'help'  => 'E-mail, на который будут отправляться сообщения с сайта'
            ),
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'settings-email',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}