<?php
namespace CatalogAdmin\Form;

use Aptero\Form\Admin\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class ProductsSettingsForm extends Form
{
    public function __construct()
    {
        parent::__construct('settings-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $help = 'Вставки:<br>{CATALOG_NAME} - Название каталога<br>{PRODUCT_NAME} - Название товара<br>{BRAND_NAME} - Производитель';

        $this->addMeta('settings-', $help);
        $this->addMeta('settings-video_', $help);
        $this->addMeta('settings-composition_', $help);
        $this->addMeta('settings-reviews_', $help);
        $this->addMeta('settings-articles_', $help);
        //$this->addMeta('props_', $help);
        //$this->addMeta('comments_', $help);
        //$this->addMeta('instruction_', $help);
        //$this->addMeta('certificate_', $help);


        $this->add(array(
            'name' => 'settings-1cLogin',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Логин',
            ),
        ));

        $this->add(array(
            'name' => 'settings-1cPassword',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Пароль',
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

        $this->setInputFilter($inputFilter);
    }
}