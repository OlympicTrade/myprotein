<?php
namespace Catalog\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class OrderStep2Form extends Form
{
    public function setModel($model)
    {
        parent::setModel($model);
    }
	
    public function setData($data)
    {
		/*if($dt = \DateTime::createFromFormat('H:i', $data['attrs-time_from'])) {
			$data['attrs-time_to'] = $dt->modify('+3 hours')->format('H:i');
		}*/

        if(strpos($data['attrs-time_from'], '-')) {
            $tmp = explode('-', $data['attrs-time_from']);

            $data['attrs-time_from'] = $tmp[0];
            $data['attrs-time_to'] = $tmp[1];
        }

        parent::setData($data);
    }

    public function __construct($type)
    {
        parent::__construct('order-form');

        $this->add([
            'name'  => 'oid',
            'type'  => 'Zend\Form\Element\Text',
        ]);

        $this->add([
            'name'  => 'attrs-delivery',
            'type'  => 'Zend\Form\Element\Text',
        ]);

        if(in_array($type, ['courier', 'post'])) {
            $this->add([
                'name'  => 'attrs-index',
                'type'  => 'Zend\Form\Element\Text',
            ]);

            $this->add([
                'name'  => 'attrs-city',
                'type'  => 'Zend\Form\Element\Text',
            ]);

            $this->add([
                'name'  => 'attrs-street',
                'type'  => 'Zend\Form\Element\Text',
            ]);

            $this->add([
                'name'  => 'attrs-house',
                'type'  => 'Zend\Form\Element\Text',
            ]);

            $this->add([
                'name'  => 'attrs-building',
                'type'  => 'Zend\Form\Element\Text',
            ]);

            $this->add([
                'name'  => 'attrs-flat',
                'type'  => 'Zend\Form\Element\Text',
            ]);

            $this->add([
                'name'  => 'attrs-date',
                'type'  => 'Zend\Form\Element\Text',
            ]);

            $this->add([
                'name' => 'attrs-time_from',
                'type'  => 'Zend\Form\Element\Text',
            ]);

            $this->add([
                'name' => 'attrs-time_to',
                'type'  => 'Zend\Form\Element\Text',
            ]);
        } elseif($type == 'pickup') {
            $this->add([
                'name' => 'attrs-point',
                'type'  => 'Zend\Form\Element\Text',
            ]);
        }
    }

    public function setFilters($type)
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput([
            'name'     => 'attrs-delivery',
            'required' => true,
            'filters'  => [
                ['name' => 'StringTrim'],
                ['name' => 'StripTags'],
            ],
        ]));

        if($type == 'courier') {
            $inputFilter->add($factory->createInput([
                'name'     => 'attrs-street',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
            ]));

            $inputFilter->add($factory->createInput([
                'name'     => 'attrs-house',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
            ]));

            $inputFilter->add($factory->createInput([
                'name'     => 'attrs-date',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
            ]));

            $inputFilter->add($factory->createInput([
                'name'     => 'attrs-time_from',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
            ]));

            $inputFilter->add($factory->createInput([
                'name'     => 'attrs-time_to',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
            ]));
        } elseif($type == 'pickup') {
            $inputFilter->add($factory->createInput([
                'name' => 'attrs-point',
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
            ]));
        }

        $this->setInputFilter($inputFilter);
    }
}