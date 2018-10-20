<?php
namespace DeliveryAdmin\Form;

use ApplicationAdmin\Model\Region;
use Aptero\Form\Form;

use DeliveryAdmin\Model\Delivery;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class DeliveryEditForm extends Form
{
    public function setModel($model)
    {
        parent::setModel($model);

        $this->get('region_id')->setOptions(array(
            'model' => new Region(),
            'sort'  => 'name',
            'empty' => ''
        ));
    }

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
                'label' => 'Регион',
            ),
        ));

        $this->add(array(
            'name' => 'region_id',
            'type'  => 'Aptero\Form\Element\TreeSelect',
            'options' => array(
                'label'   => 'Регион',
            ),
        ));

        $this->add(array(
            'name' => 'delay',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Задержка при доставке',
            ),
        ));

        $this->add(array(
            'name' => 'pickup_text',
            'type'  => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label' => 'Описание',
            ),
            'attributes'=>array(
                'class' => 'editor',
                'id'    => 'page-text'
            ),
        ));

        $this->add(array(
            'name' => 'delivery_text',
            'type'  => 'Zend\Form\Element\Textarea',
            'attributes'=>array(
                'class' => 'editor',
                'id'    => 'page-text'
            ),
            'options' => array(
                'label' => 'Время доставки',
            ),
        ));

        $this->add(array(
            'name' => 'delay_text',
            'type'  => 'Zend\Form\Element\Textarea',
            'attributes'=>array(
                'class' => 'editor',
                'id'    => 'page-text'
            ),
        ));

        $this->add(array(
            'name' => 'pickup_income',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Цена для клиента',
            ),
        ));

        $this->add(array(
            'name' => 'pickup_outgo',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Себестоимость',
            ),
        ));

        $this->add(array(
            'name' => 'pickup_free',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Бесплатно от',
            ),
        ));

        $this->add(array(
            'name' => 'courier_income',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Цена для клиента',
            ),
        ));

        $this->add(array(
            'name' => 'courier_outgo',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Себестоимость',
            ),
        ));

        $this->add(array(
            'name' => 'courier_free',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Бесплатно от',
            ),
        ));

        $this->add(array(
            'name' => 'courier_company',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'options' => Delivery::$companies,
                'label' => 'Курьеры',
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