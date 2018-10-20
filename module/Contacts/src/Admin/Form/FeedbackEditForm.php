<?php
namespace ContactsAdmin\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class FeedbackEditForm extends Form
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
            'name' => 'name',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'ФИО',
            ),
        ));

        $this->add(array(
            'name' => 'phone',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Контакты',
            ),
        ));

        $this->add(array(
            'name' => 'question',
            'type'  => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label' => 'Сообщение',
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