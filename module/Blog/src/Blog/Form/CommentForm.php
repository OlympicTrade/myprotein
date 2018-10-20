<?php
namespace Blog\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class CommentForm extends Form
{
    public function __construct()
    {
        parent::__construct('feedback-form');

        $this->add(array(
            'name' => 'article_id'
        ));

        $this->add(array(
            'name' => 'parent'
        ));

        $this->add(array(
            'name' => 'name'
        ));

        $this->add(array(
            'name' => 'comment'
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
            'name'     => 'comment',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
                array('name' => 'StripTags'),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'article_id',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'Zend\Validator\Db\RecordExists',
                    'options' => array(
                        'table' => 'articles',
                        'field' => 'id',
                        'adapter' => \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::getStaticAdapter()
                    ),
                ),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'parent',
            'required' => false,
            'validators' => array(
                array(
                    'name' => 'Zend\Validator\Db\RecordExists',
                    'options' => array(
                        'table' => 'articles_comments',
                        'field' => 'id',
                        'adapter' => \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::getStaticAdapter()
                    ),
                ),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}