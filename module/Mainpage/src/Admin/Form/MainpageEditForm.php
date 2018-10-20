<?php
namespace MainpageAdmin\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class MainpageEditForm extends Form
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
            'name' => 'logo_desc',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Подпись',
            ),
        ));

        $this->add(array(
            'name' => 'events_title',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Заголовок',
            ),
        ));

        $this->add(array(
            'name' => 'slider_title',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Заголовок',
            ),
        ));

        $this->add(array(
            'name' => 'slider_text',
            'type'  => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label' => 'Текст',
            ),
        ));

        $this->add(array(
            'name' => 'text_title',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Заголовок',
            ),
        ));

        $this->add(array(
            'name' => 'text_desc',
            'type'  => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label' => 'Текст',
            ),
        ));

        $this->add(array(
            'name' => 'adv_1_text',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Преимущество 1',
            ),
        ));

        $this->add(array(
            'name' => 'adv_2_text',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Преимущество 2',
            ),
        ));

        $this->add(array(
            'name' => 'adv_3_text',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Преимущество 3',
            ),
        ));

        $this->add(array(
            'name' => 'adv_4_text',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Преимущество 4',
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