<?php
namespace AthletesAdmin\Form;

use Aptero\Form\Admin\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class AthletesEditForm extends Form
{
    public function setModel($model)
    {
        parent::setModel($model);

        $this->get('image-image')->setOptions(array(
            'model' => $model->getPlugin('image'),
        ));

        $this->get('images-images')->setOptions(array(
            'model' => $model->getPlugin('images')
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
        
        $this->addMeta('', '', '');

        $this->add(array(
            'name' => 'name',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Имя',
            ),
        ));

        $this->add(array(
            'name' => 'surname',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Фамилия',
            ),
        ));

        $this->add(array(
            'name' => 'url',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Url',
            ),
        ));

        $this->add(array(
            'name' => 'sort',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Сортировка',
            ),
        ));

        $this->add(array(
            'name' => 'sport',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Спорт',
            ),
        ));

        $this->add(array(
            'name' => 'video_1',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Видео 1',
            ),
        ));

        $this->add(array(
            'name' => 'video_2',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Видео 2',
            ),
        ));

        $this->add(array(
            'name' => 'video_3',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Видео 3',
            ),
        ));

        $this->add(array(
            'name' => 'image-image',
            'type'  => 'Aptero\Form\Element\Admin\Image',
            'options' => array(),
        ));

        $this->add(array(
            'name' => 'images-images',
            'type'  => 'Aptero\Form\Element\Admin\Images',
            'options' => array(),
        ));

        $this->add(array(
            'name' => 'text',
            'type'  => 'Zend\Form\Element\Textarea',
            'attributes'=>array(
                'class' => 'editor',
                'id'    => 'page-text'
            ),
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'name',
            'required' => true,
        )));

        $this->setInputFilter($inputFilter);
    }
}