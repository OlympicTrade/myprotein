<?php
namespace NewsAdmin\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class NewsSettingsForm extends Form
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
                'help'  => 'Вставки:<br>{NEWS_NAME} - Название новости<br>{NEWS_DATE} - Дата<br>{NEWS_AUHOR} - Автор'
            ),
        ));

        $this->add(array(
            'name' => 'settings-keywords',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Ключевые слова (Keywords)',
                'help'  => 'Вставки:<br>{NEWS_NAME} - Название новости<br>{NEWS_DATE} - Дата<br>{NEWS_AUHOR} - Автор'
            ),
        ));

        $this->add(array(
            'name' => 'settings-description',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Описание (Description)',
                'help'  => 'Вставки:<br>{NEWS_NAME} - Название новости<br>{NEWS_DATE} - Дата<br>{NEWS_AUHOR} - Автор'
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