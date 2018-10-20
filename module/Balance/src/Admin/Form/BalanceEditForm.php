<?php
namespace BalanceAdmin\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class BalanceEditForm extends Form
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
                'label' => 'Название',
            ),
        ));

        $this->add(array(
            'name' => 'income',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Доход',
            ),
        ));

        $this->add(array(
            'name' => 'outgo',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Расход',
            ),
        ));

        $this->add(array(
            'name' => 'desc',
            'type'  => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label' => 'Комментарий',
            ),
        ));

        $this->add(array(
            'name' => 'date',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Дата',
            ),
            'attributes' => array(
                'class' => 'datepicker'
            )
        ));

        /*$this->add(array(
            'name' => 'select',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'options' => array(
                    1 => 'Пункт 1',
                    2 => 'Пункт 2',
                    3 => 'Пункт 3'
                ),
                'label' => 'Селект',
            ),
        ));*/
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'name',
            'required' => false,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}