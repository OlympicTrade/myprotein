<?php
namespace CatalogAdmin\Form;

use Aptero\Form\Admin\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class CatalogSettingsForm extends Form
{
    public function __construct()
    {
        parent::__construct('settings-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $help = 'Вставки:<br>{CATALOG_NAME} - Каталог<br>{CATALOG_NAME_L} - Каталог в нижнем регистре';
        $this->addMeta('settings-', $help);

        $help = 'Вставки:<br>{CATALOG_NAME} - Каталог<br>{CATALOG_NAME_L} - Каталог в нижнем регистре<br>{BRAND_NAME} - Производитель';
        $this->addMeta('settings-brand_', $help);
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