<?php
namespace CatalogAdmin\Form;

use Aptero\Form\Form;

use CatalogAdmin\Model\Reviews;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class ReviewsEditForm extends Form
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
            'name' => 'review',
            'type'  => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label' => 'Комментарий',
            ),
        ));

        $this->add(array(
            'name' => 'answer',
            'type'  => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label' => 'Ответ',
            ),
        ));

        $this->add(array(
            'name' => 'email',
            'type'  => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label' => 'E-mail',
            ),
        ));

        $this->add(array(
            'name' => 'time_create',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Дата отзыва',
            ),
        ));

        $this->add(array(
            'name' => 'status',
            'type' => 'Zend\Form\Element\Radio',
            'options' => array(
                'options' => Reviews::$statuses,
                'label'   => 'Статус',
            ),
        ));

        $this->add(array(
            'name' => 'stars',
            'type' => 'Zend\Form\Element\Radio',
            'options' => array(
                'options' => array(
                    1 => '1',
                    2 => '2',
                    3 => '3',
                    4 => '4',
                    5 => '5',
                ),
                'label' => 'Звезды',
            ),
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'name',
            'required' => true,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}