<?php
namespace TasksAdmin\Form;

use UserAdmin\Model\User;
use Zend\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class TasksFilterForm extends Form
{
    public function __construct()
    {
        parent::__construct('UserFilter');

        $this->setAttributes(array(
            'method'  => 'get',
            'class'   => 'list-form',
        ));

        $this->add(array(
            'name' => 'search',
        ));

        $user = new User();
        $users = $user->getCollection();
        $users->select()->where(array('type' => User::ROLE_ADMIN));

        $this->add(array(
            'name' => 'user_id',
            'type'  => 'Aptero\Form\Element\TreeSelect',
            'options' => array(
                'collection' => $users,
                'empty'      => 'Все исполнители',
            ),
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

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