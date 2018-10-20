<?php
namespace UserAdmin\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

use User\Model\User;

class UserEditForm extends Form
{
    public function setData($data)
    {
        parent::setData($data);

        $this->get('password')->setValue('');
    }

    public function setModel($model)
    {
        parent::setModel($model);

        $this->get('image-image')->setOptions(array(
            'model' => $model->getPlugin('image'),
        ));
    }

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
            'name' => 'name',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'ФИО',
            ),
        ));

        $this->add(array(
            'name' => 'login',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Логин',
                'help'  => 'от 4 до 20 латинских символов и цифр'
            ),
        ));

        $this->add(array(
            'name' => 'image-image',
            'type'  => 'Aptero\Form\Element\Admin\Image',
            'options' => array(
                //'label'   => 'Фото'
            ),
        ));

        $this->add(array(
            'name' => 'type',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'options' => array(
                    User::ROLE_REGISTERED => 'Пользователь',
                    User::ROLE_ADMIN      => 'Администратор',
                    User::ROLE_EDITOR     => 'Менеджер'
                ),
                'label' => 'Статус',
            ),
        ));

        $this->add(array(
            'name' => 'attrs-phone',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Телефон'
            )
        ));

        $this->add(array(
            'name' => 'attrs-name',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Телефон'
            )
        ));

        $this->add(array(
            'name' => 'attrs-contact',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Телефон'
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'email',
            'options' => array(
                'label' => 'Email'
            )
        ));

        $this->add(array(
            'name' => 'attrs-skype',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Skype'
            )
        ));


        $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'type'  => 'password',
            ),
            'options' => array(
                'label' => 'Пароль',
                'help'  => 'от 6 до 30 латинских символов и цифр'
            )
        ));

        $this->add(array(
            'name' => 'password_repeat',
            'attributes' => array(
                'type'  => 'password',
            ),
            'options' => array(
                'label' => 'Еще раз'
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'active',
            'options' => array(
                'label' => 'Пользователь активен',
                'value_options' => array(
                    '1' => 'Да',
                    '0' => 'Нет',
                ),
                'help' => 'Неактивный пользователь не сможет зайти в свой акканут'
            ),
            'attributes' => array(
                'value' => '1'
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'confirm',
            'options' => array(
                'label' => 'Пользователь подтвержен',
                'value_options' => array(
                    '1' => 'Да',
                    '0' => 'Нет',
                ),
                'help' => 'Подтверждение email адреса пользователя'
            ),
            'attributes' => array(
                'value' => '1'
            )
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'login',
            'required' => false,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name'    => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min'      => 4,
                        'max'      => 20,
                    ),
                ),
                array(
                    'name'    => 'Regex',
                    'options' => array(
                        'pattern' => '/^[a-zA-Z1-9]*$/',
                        'messages' => array(
                            \Zend\Validator\Regex::NOT_MATCH => 'Allowed only Latin characters and digits',
                        ),
                    ),
                ),
                array(
                    'name'    => 'Db\NoRecordExists',
                    'options' => array(
                        'table'     => 'users',
                        'field'     => 'login',
                        'adapter'   => $this->getModel()->getDbAdapter(),
                        'exclude' => array(
                            'field' => 'login',
                            'value' => $this->getModel()->login
                        )
                    ),
                ),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'email',
            'required' => true,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name'    => 'EmailAddress',
                ),
                array(
                    'name'    => 'Db\NoRecordExists',
                    'options' => array(
                        'table'     => 'users',
                        'field'     => 'email',
                        'adapter'   => $this->getModel()->getDbAdapter(),
                        'exclude' => array(
                            'field' => 'email',
                            'value' => $this->getModel()->email
                        )
                    ),
                ),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'password',
            'required' => false,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name'    => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min'      => 6,
                        'max'      => 30,
                    ),
                ),
                array(
                    'name'    => 'Regex',
                    'options' => array(
                        'pattern' => '/^[a-zA-Z1-9]*$/'
                    ),
                ),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'password_repeat',
            'required' => false,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name'    => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min'      => 6,
                        'max'      => 30,
                    ),
                ),
                array(
                    'name' => 'Identical',
                    'options' => array(
                        'token' => 'password',
                    ),
                ),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}