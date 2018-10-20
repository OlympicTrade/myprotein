<?php
namespace ApplicationAdmin\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class PageSettingsForm extends Form
{
    public function setModel($model)
    {
        parent::setModel($model);
    }

    public function __construct()
    {
        parent::__construct('settings-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $this->add(array(
            'name' => 'settings-title',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Заголовок (Title)',
                'help'  => 'Вставки:<br>{PAGE_NAME} - Название страницы<br>{SITE} - Название страницы<br>{DOMAIN} - Название страницы'
            ),
        ));

        $this->add(array(
            'name' => 'settings-keywords',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Ключевые слова (Keywords)',
                'help'  => 'Вставки:<br>{PAGE_NAME} - Название страницы<br>{SITE} - Название страницы<br>{DOMAIN} - Название страницы'
            ),
        ));

        $this->add(array(
            'name' => 'settings-description',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Описание (Description)',
                'help'  => 'Вставки:<br>{PAGE_NAME} - Название страницы<br>{SITE} - Название страницы<br>{DOMAIN} - Название страницы'
            ),
        ));

        $this->add(array(
            'name' => 'settings-site',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Название сайта',
                'help'  => 'Используется в письмах, заголовках и т.д.'
            ),
        ));

        $this->add(array(
            'name' => 'settings-domain',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Домен',
                'help'  => 'Используется в письмах, заголовках и т.д.'
            ),
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'settings-title',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'settings-site',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'settings-domain',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}