<?php
namespace ApplicationAdmin\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class SettingsEditForm extends Form
{
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
            'name' => 'site_name',
            'options' => array(
                'label'     => 'Название сайта',
                'required'  => true,
                'help'      => 'Используется в заголовках писем, микроразметке и т.д.',
            ),
        ));

        $this->add(array(
            'name' => 'site_color',
            'options' => array(
                'label'     => 'Осн. цвет сайта (HEX)',
                'required'  => false,
                'help'      => 'Используется в микроразметке',
            ),
        ));

        $this->add(array(
            'name' => 'site_logo',
            'options' => array(
                'label'     => 'URL логотипа',
                'required'  => true,
                'help'      => 'Используется в микроразметке',
            ),
        ));

        $this->add(array(
            'name' => 'domain',
            'options' => array(
                'label'     => 'Домен (основной)',
                'required'  => true
            ),
        ));

        $this->add(array(
            'name' => 'mdomain',
            'options' => array(
                'label'     => 'Домен (моб. версия)',
                'required'  => true
            ),
        ));

        $this->add(array(
            'name' => 'html_body',
            'type'  => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label'     => 'Тег body',
                'help'      => 'Яндек Метрка, Google Analtics или друой код, который необходимо вставить перед закрытием тега body',
            ),
        ));

        $this->add(array(
            'name' => 'html_head',
            'type'  => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label'     => 'Тег head',
                'help'      => 'Код вставляеться в конец тега head',
            ),
        ));

        $this->add(array(
            'name' => 'robots',
            'type'  => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label'     => 'Robots.txt',
            ),
        ));

        $this->add(array(
            'name' => 'mail_sender',
            'options' => array(
                'label'     => 'Имя отправителя',
                'required'  => true
            ),
        ));

        $this->add(array(
            'name' => 'mail_email',
            'options' => array(
                'label'     => 'Email',
                'required'  => true
            ),
        ));

        $this->add(array(
            'name' => 'mail_password',
            'options' => array(
                'label'     => 'Пароль',
                'required'  => true
            ),
        ));

        $this->add(array(
            'name' => 'mail_smtp',
            'options' => array(
                'label'     => 'SMTP сервер',
                'required'  => true
            ),
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'site_name',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'site_logo',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'domain',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'mail_sender',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'mail_email',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'mail_password',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'mail_smtp',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}