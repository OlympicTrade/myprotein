<?php
namespace ApplicationAdmin\Form;

use ApplicationAdmin\Model\MenuItems;
use ApplicationAdmin\Model\Page;
use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use ApplicationAdmin\Model\Menu;

class MenuItemsEditForm extends Form
{
    public function setModel($model)
    {
        parent::setModel($model);

        $this->get('page_id')->setOption('model', new Page());

        $menuItems = new MenuItems();
        if($model->get('menu_id')) {
            $menuItems->select()->where(array('menu_id', $model->get('menu_id')));
        } else {
            $menuItems->select()->where(array('menu_id', (int) $_GET['menu']));
        }

        $this->get('parent')->setOption('model', $menuItems);
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
                'label'     => 'Название',
            )
        ));

        $this->add(array(
            'name' => 'type',
            'type' => 'Zend\Form\Element\Radio',
            'options' => array(
                'options' => array(
                    1 => 'Страница',
                    2 => 'URL адрес',
                ),
                'label' => 'Тип',
            ),
        ));

        $this->add(array(
            'name' => 'active',
            'type' => 'Zend\Form\Element\Radio',
            'options' => array(
                'options' => array(
                    1 => 'Активен',
                    0 => 'Не активен',
                ),
                'label' => 'Пункт активен',
            ),
        ));

        $weight = array();
        for($i = 0; $i < 100; $i++) { $weight[$i] = $i; }

        $this->add(array(
            'name' => 'sort',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'options' => $weight,
                'label' => 'Вес (сортировка)',
                'help'  => 'Чем больше вес, тем дальше элемент в списке меню'
            ),
        ));

        $this->add(array(
            'name' => 'page_id',
            'type'  => 'Aptero\Form\Element\TreeSelect',
            'options' => array(
                'label'   => 'Страница',
            ),
        ));

        $this->add(array(
            'name' => 'parent',
            'type'  => 'Aptero\Form\Element\TreeSelect',
            'options' => array(
                'label'   => 'Родительский елемент',
                'empty'   => ''
            ),
        ));

        $this->add(array(
            'name' => 'url',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label'     => 'URL',
            )
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
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'url',
            'required' => false,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            )
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'page_id',
            'required' => false,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            )
        )));

        $this->setInputFilter($inputFilter);
    }
}