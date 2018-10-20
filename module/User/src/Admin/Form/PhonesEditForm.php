<?php
namespace UserAdmin\Form;

use Aptero\Form\Form;

use UserAdmin\Model\Phone;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

use User\Model\User;

class PhonesEditForm extends Form
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
            'name' => 'phone',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Телефон',
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'confirmed',
            'options' => array(
                'label' => 'Подтверждение',
                'value_options' => Phone::$confirmStatuses
            ),
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $this->setInputFilter($inputFilter);
    }
}