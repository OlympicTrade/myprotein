<?php
namespace DiscountsAdmin\Form;

use Aptero\Form\Form;

use CatalogAdmin\Model\Products;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class DiscountsEditForm extends Form
{
    public function setModel($model)
    {
        parent::setModel($model);

        $this->get('image-image')->setOptions(array(
            'model' => $model->getPlugin('image'),
        ));
        
        $this->get('products-collection')->setOption('model', $model->getPlugin('products'));
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
                'label' => 'Название',
            ),
        ));

        $this->add(array(
            'name' => 'discount',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Скидка до',
            ),
        ));

        $this->add(array(
            'name' => 'color',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Цвет текста',
            ),
        ));

        $this->add(array(
            'name' => 'background',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Фон',
            ),
        ));

        $this->add(array(
            'name' => 'border',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Рамка',
            ),
        ));

        $this->add(array(
            'name' => 'shape',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'options' => array(
                    'square' => 'Прямоугольник',
                    'circle' => 'Круг',
                ),
                'label' => 'Форма',
            ),
        ));

        $this->add(array(
            'name' => 'date_from',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label'   => 'Дата c',
            ),
            'attributes'=>array(
                'class'     => 'datepicker',
            ),
        ));

        $this->add(array(
            'name' => 'date_to',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label'   => 'Дата по',
            ),
            'attributes'=>array(
                'class'     => 'datepicker',
            ),
        ));

        $this->add(array(
            'name' => 'image-image',
            'type'  => 'Aptero\Form\Element\Admin\Image',
            'options' => array(),
        ));

        $this->add(array(
            'name' => 'products-collection',
            'type'  => 'Aptero\Form\Element\Admin\Collection',
            'options' => array(
                'options'      => array(
                    'product_id' => array(
                        'label'   => 'Товары',
                        'width'   => 200,
                        'options' => new Products()
                    ),
                    'discount' => array(
                        'label'   => 'Скидка (%)',
                        'width'   => 150,
                    ),
                )
            ),
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'name',
            'required' => false,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}