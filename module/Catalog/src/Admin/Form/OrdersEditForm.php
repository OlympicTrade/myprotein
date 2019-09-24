<?php
namespace CatalogAdmin\Form;

use Aptero\Form\Admin\Form;

use CatalogAdmin\Model\Orders;
use DeliveryAdmin\Model\City;
use DeliveryAdmin\Model\Delivery;
use DeliveryAdmin\Model\Point;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class OrdersEditForm extends Form
{
    public function setModel($model)
    {
        parent::setModel($model);

		$cities = City::getEntityCollection();
		$cities->select()->where->notEqualTo('points', 0);
        $this->get('city_id')->setOptions(array(
            'collection' => $cities,
            'empty'   => ''
        ));

        $points = Point::getEntityCollection();
        if($model->get('city_id')) {
            $points->select()
                ->columns(['id', 'name' => 'address'])
                ->where(['city_id' => $model->get('city_id')]);
        }
        $this->get('attrs-point')->setOptions([
            'collection' => $points
        ]);
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
            'name' => 'status',
            'type'  => 'Zend\Form\Element\Select',
            'options' => [
                'label' => 'Статус заказа',
                'options' => Orders::$processStatuses,
            ],
        ]);

        $this->add([
            'name' => 'paid',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Оплачено',
            ],
        ]);

        $this->add([
            'name' => 'description',
            'type'  => 'Zend\Form\Element\Textarea',
            'options' => [
                'label' => 'Дополнительная информация',
            ],
        ]);

        $this->add([
            'name' => 'city_id',
            'type'  => 'Aptero\Form\Element\TreeSelect',
            'options' => [
                'label'   => 'Город',
            ],
        ]);

        /*$this->add([
            'name' => 'attrs-address',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Адрес',
            ],
        ]);*/

        $this->add([
            'name' => 'attrs-street',
            'type'  => 'Zend\Form\Element\Text',
            'attributes' => [
                'placeholder' => 'Улица',
            ],
        ]);

        $this->add([
            'name' => 'attrs-index',
            'type'  => 'Zend\Form\Element\Text',
            'attributes' => [
                'placeholder' => 'Индекс',
            ],
        ]);

        $this->add([
            'name' => 'attrs-city',
            'type'  => 'Zend\Form\Element\Text',
            'attributes' => [
                'placeholder' => 'Город',
            ],
        ]);

        $this->add([
            'name' => 'attrs-house',
            'type'  => 'Zend\Form\Element\Text',
            'attributes' => [
                'placeholder' => 'Дом',
            ],
        ]);

        $this->add([
            'name' => 'attrs-building',
            'type'  => 'Zend\Form\Element\Text',
            'attributes' => [
                'placeholder' => 'Корпус',
            ],
        ]);

        $this->add([
            'name' => 'attrs-flat',
            'type'  => 'Zend\Form\Element\Text',
            'attributes' => [
                'placeholder' => 'Квартира/Офис',
            ],
        ]);

        $time = array('' => '');
        for($i = 9; $i <= 21; $i++) {
            $tmp = str_pad($i, 2, '0', STR_PAD_LEFT);
            $time[$tmp . ':00'] = $tmp . ':00';
        }

        $this->add([
            'name' => 'attrs-time_from',
            'type'  => 'Zend\Form\Element\Select',
            'options' => [
                'label' => 'Время с',
                'options' => $time,
            ],
        ]);

        $this->add([
            'name' => 'attrs-time_to',
            'type'  => 'Zend\Form\Element\Select',
            'options' => [
                'label' => 'Время по',
                'options' => $time,
            ],
        ]);

        $this->add([
            'name' => 'attrs-date',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label'   => 'Дата',
            ],
            'attributes'=> [
                'class'     => 'datepicker',
                'data-date' => 'dd.mm.yy',
            ],
        ]);

        $this->add([
            'name' => 'attrs-delivery',
            'type'  => 'Zend\Form\Element\Select',
            'options' => [
                'label' => 'Тип доставки',
                'options' => [
                    ''         => 'Не выбран',
                    'pickup'   => 'Самовывоз',
                    'express'  => 'Экспресс',
                    'courier'  => 'Курьер',
                    'post'     => 'Почта',
                ],
            ],
        ]);

        $this->add([
            'name' => 'attrs-point',
            'type'  => 'Aptero\Form\Element\TreeSelect',
            'options' => [
                'label'   => 'Точка выдачи',
                'model' => new Point(),
                'empty' => 'Не выбрана',
                'sort'  => 'name',
                'field' => 'name',
            ],
        ]);

        $this->add([
            'name' => 'delivery_company',
            'type'  => 'Zend\Form\Element\Select',
            'options' => [
                'label' => 'Доставщик',
                'options' => [
                    Delivery::COMPANY_INDEX_EXPRESS => 'Индекс Экспресс',
                    Delivery::COMPANY_GLAVPUNKT     => 'Главпункт',
                    Delivery::COMPANY_UNKNOWN       => 'Не выбрана',
                ],
            ],
        ]);
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'attrs-delivery',
            'required' => false,
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'attrs-time_from',
            'required' => false,
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'attrs-time_to',
            'required' => false,
        )));

        $this->setInputFilter($inputFilter);
    }
}