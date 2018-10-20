<?php
namespace MetricsAdmin\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class MetricsForm extends Form
{
    public function __construct()
    {
        parent::__construct('edit-form');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'type',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'options' => array(
                    'assets'      => 'Активы подробно',
                    'assets_sum'  => 'Активы суммарно',
                    'orders_avg'  => 'Среднее кол-во заказов',
                    'check_avg'   => 'Средний чек',
                    'orders_sum'  => 'Кол-во заказов',
                    'income_sum'  => 'Суммарный доход',
                ),
                'label' => 'Тип',
            ),
        ));

        $this->add(array(
            'name' => 'date_from',
            'type' => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Дата с',
            ),
            'attributes' => array(
                'class' => 'datepicker'
            )
        ));

        $this->add(array(
            'name' => 'date_to',
            'type' => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Дата по',
            ),
            'attributes' => array(
                'class' => 'datepicker'
            )
        ));

        $this->add(array(
            'name' => 'interval',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'options' => array(
                    'day'     => 'День',
                    'week'    => 'Неделя',
                    'month'   => 'Месяц',
                ),
                'label' => 'Интервал',
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