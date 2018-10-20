<?php
namespace Contacts\Form;

use Aptero\Form\Form;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature as StaticDbAdapter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class SubscribeForm extends Form
{
    public function __construct()
    {
        parent::__construct('feedback-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $this->add(array(
            'name' => 'email'
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'email',
            'required' => true,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
                array('name' => 'StringToLower'),
            ),
            'validators' => array(
                array(
                    'name'    => 'EmailAddress',
                ),
                array(
                    'name'    => 'Db\NoRecordExists',
                    'options' => array(
                        'table'     => 'subscribe',
                        'field'     => 'email',
                        'adapter'   => StaticDbAdapter::getStaticAdapter(),
                        'messages' => array(
                            \Zend\Validator\Db\NoRecordExists::ERROR_RECORD_FOUND => 'Вы уже подписаны',
                        ),
                    ),
                ),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}