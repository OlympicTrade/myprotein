<?php
namespace CallcenterAdmin\Form;

use Aptero\Form\Form;

use CallcenterAdmin\Model\Call;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class CallcenterEditForm extends Form
{
    public function __construct()
    {
        parent::__construct('edit-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $this->add([
            'name' => 'id',
            'type'  => 'Zend\Form\Element\Hidden',
        ]);

        $this->add([
            'name' => 'theme',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Тема',
            ],
        ]);

        $this->add([
            'name' => 'desc',
            'type'  => 'Zend\Form\Element\Textarea',
            'options' => [
                'label' => 'Описание',
            ],
        ]);

        $this->add([
            'name' => 'name',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'ФИО',
            ],
        ]);

        $this->add([
            'name' => 'type_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                'options' => Call::$types,
                'label' => 'Тип',
            ],
        ]);

        $this->add([
            'name' => 'status',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                'options' => Call::$statuses,
                'label' => 'Статус',
            ],
        ]);
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $this->setInputFilter($inputFilter);
    }
}