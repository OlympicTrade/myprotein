<?php
namespace CatalogAdmin\Form;

use Aptero\Form\Form;

use CatalogAdmin\Model\Requests;
use CatalogAdmin\Model\Reviews;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class RequestsEditForm extends Form
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
            'name' => 'contact',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Конакты',
            ),
        ));

        $this->add([
            'name' => 'status',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                'options' => Requests::$statuses,
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