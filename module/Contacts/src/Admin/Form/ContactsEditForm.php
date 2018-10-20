<?php
namespace ContactsAdmin\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class ContactsEditForm extends Form
{
    public function __construct()
    {
        parent::__construct('edit-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $this->add(array(
            'name' => 'id',
            'type'  => 'Zend\Form\Element\Hidden',
        ));

        $this->add(array(
            'name' => 'email',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'E-mail',
            ),
        ));

        $this->add(array(
            'name' => 'address',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Адрес',
            ),
        ));

        $this->add(array(
            'name' => 'phone_1',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Телефон 1',
            ),
        ));

        $this->add(array(
            'name' => 'phone_2',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Телефон 2',
            ),
        ));

        $this->add(array(
            'name' => 'phone_3',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Телефон 3',
            ),
        ));

        $this->add(array(
            'name' => 'skype',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Skype',
            ),
        ));

        $this->add(array(
            'name' => 'vkontakte',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Вконтакте',
            ),
        ));

        $this->add(array(
            'name' => 'facebook',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Facebook',
            ),
        ));

        $this->add(array(
            'name' => 'youtube',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Youtube',
            ),
        ));

        $this->add(array(
            'name' => 'latitude',
            'type'  => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'id' => 'latitude',
            ),
        ));

        $this->add(array(
            'name' => 'longitude',
            'type'  => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'id' => 'longitude',
            ),
        ));

        $this->add(array(
            'name' => 'show_map',
            'type' => 'Zend\Form\Element\Radio',
            'options' => array(
                'options' => array(
                    1 => 'Да',
                    0 => 'Нет',
                ),
                'label' => 'Показать карту',
            ),
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'email',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}