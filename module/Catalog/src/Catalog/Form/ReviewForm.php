<?php
namespace Catalog\Form;

use Aptero\Form\Form;

use User\Service\AuthService;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class ReviewForm extends Form
{
    public function __construct()
    {
        parent::__construct('feedback-form');

        $this->add(array(
            'name' => 'product_id'
        ));

        $this->add(array(
            'name' => 'stars'
        ));

        $this->add(array(
            'name' => 'name'
        ));

        $this->add(array(
            'name' => 'email'
        ));

        $this->add(array(
            'name' => 'review'
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
                array('name' => 'StringTrim'),
                array('name' => 'StripTags'),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'review',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
                array('name' => 'StripTags'),
            ),
        )));

        if(!AuthService::hasUser()) {
            $inputFilter->add($factory->createInput(array(
                'name' => 'email',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim'),
                    array('name' => 'StripTags'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'EmailAddress',
                    ),
                ),
            )));
        }

        $inputFilter->add($factory->createInput(array(
            'name'     => 'stars',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
                array('name' => 'StripTags'),
            ),
            'validators' => array(
                array(
                    'name' => 'Zend\Validator\Between',
                    'options' => array(
                        'min' => 1,
                        'max' => 5,
                        'messages' => array(
                            'notBetween' => 'Поставьте оценку',
                        ),
                    ),
                ),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'product_id',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'Zend\Validator\Db\RecordExists',
                    'options' => array(
                        'table' => 'products',
                        'field' => 'id',
                        'adapter' => \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::getStaticAdapter()
                    ),
                ),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}