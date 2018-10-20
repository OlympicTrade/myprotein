<?php
namespace CatalogAdmin\Form;

use Application\Model\Module;
use Aptero\Form\Form;
use CatalogAdmin\Model\Supplies;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class SuppliesEditForm extends Form
{
    public function setData($data)
    {
        if(!$data['currency_rate']) {
            $settings = Module::getSettings('Catalog', 'Supplies');
            $data['currency_rate'] = $settings->get('euro');
        }

        return parent::setData($data);
    }

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
            'name' => 'number',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Номер заказа',
            ],
        ]);

        $this->add([
            'name' => 'login',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Логин',
            ],
        ]);

        $this->add([
            'name' => 'weight',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Вес',
            ],
        ]);

        $this->add([
            'name' => 'currency_rate',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label'   => 'Курс валюты',
            ],
        ]);

        $this->add([
            'name' => 'delivery',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Доставка',
            ],
        ]);

        $this->add([
            'name' => 'desc',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Комментарии',
            ],
        ]);

        $this->add([
            'name' => 'user_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                'options' => ['' => ''] + Supplies::$users,
                'label' => 'ФИО',
            ],
        ]);

        $this->add([
            'name' => 'status',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                'options' => Supplies::$statuses,
                'label' => 'Статус',
            ],
        ]);

        $this->add([
            'name' => 'date',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label'   => 'Дата',
            ],
            'attributes'=> [
                'class'     => 'datepicker',
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