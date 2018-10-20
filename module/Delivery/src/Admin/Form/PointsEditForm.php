<?php
namespace DeliveryAdmin\Form;

use ApplicationAdmin\Model\Region;
use Aptero\Form\Form;

use DeliveryAdmin\Model\City;
use DeliveryAdmin\Model\Delivery;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class PointsEditForm extends Form
{
    public function setModel($model)
    {
        parent::setModel($model);

        $this->get('city_id')->setOptions(array(
            'model' => new City(),
            'sort'  => 'name',
            'empty' => ''
        ));

        $this->get('images-images')->setOptions([
            'model'   => $model->getPlugin('images'),
            'product' => $model,
        ]);
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
            'name' => 'latitude',
            'type'  => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'id' => 'latitude',
            ),
        ));

        $this->add(array(
            'name' => 'longitude',
            'type'  => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'id' => 'longitude',
            ),
        ));

        $this->add(array(
            'name' => 'address',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Адрес',
            ),
        ));


        $this->add(array(
            'name' => 'price',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Себестоимость',
            ),
        ));

        $this->add(array(
            'name' => 'route',
            'type'  => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label' => 'Маршрут',
            ),
        ));

        $this->add(array(
            'name' => 'phone',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Телефон',
            ),
            'attributes' => array(
                'placeholder' => '+7 (___) ___-__-__',
            ),
        ));

        $this->add(array(
            'name' => 'city_id',
            'type'  => 'Aptero\Form\Element\TreeSelect',
            'options' => array(
                'label'   => 'Регион',
            ),
        ));

        $this->add(array(
            'name' => 'worktime',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Время работы',
            ),
            'attributes' => array(
                'placeholder' => 'Пример: Будни: 11 - 20 Сб: не работает Вс: не работает',
            ),
        ));

        $this->add([
            'name' => 'delay',
            'type' => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Срок доставки (дни)',
            ],
        ]);

        $this->add([
            'name' => 'fitting',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                'options' => [0 => 'Нет', 1 => 'Есть'],
                'label' => 'Примерка оджеды',
            ],
        ]);

        $this->add([
            'name' => 'shoes',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                'options' => [0 => 'Нет', 1 => 'Есть'],
                'label' => 'Примерка обуви',
            ],
        ]);

        $this->add([
            'name' => 'partial',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                'options' => [0 => 'Нет', 1 => 'Есть'],
                'label' => 'Частичный выкуп',
            ],
        ]);

        $this->add([
            'name' => 'payment_cards',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                'options' => [0 => 'Нет', 1 => 'Есть'],
                'label' => 'Оплата картой',
            ],
        ]);

        $this->add([
            'name' => 'city',
            'type' => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Город (письменно)',
            ],
        ]);

        $this->add([
            'name' => 'images-images',
            'type'  => 'Aptero\Form\Element\Admin\Images',
            'options' => [],
        ]);
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $this->setInputFilter($inputFilter);
    }
}