<?php
namespace UserAdmin\Form;

use Zend\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

use User\Model\User;

class UserFilterForm extends Form
{
    public function __construct()
    {
        parent::__construct('UserFilter');

        $this->setAttribute('method', 'get');

        $this->add(array(
            'name' => 'type',
            'type' => 'Zend\Form\Element\MultiCheckbox',
            'options' => array(
                'label' => 'Статус',
                'value_options' => array(
                    User::ROLE_REGISTERED => 'Пользователь',
                    User::ROLE_ADMIN      => 'Администратор',
                    User::ROLE_EDITOR     => 'Менеджер'
                ),
            ),
            'attributes' => array(
                //'value' => '1'
            ),
        ));

        $this->add(array(
            'name' => 'search',
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'type',
            'required' => false,
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'search',
            'required' => false,
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}