<?php
namespace ApplicationAdmin\Form;

use Aptero\Form\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use ApplicationAdmin\Model\Menu;

class ContentEditForm extends Form
{
    public function setModel($model)
    {
        parent::setModel($model);

        $this->get('image-image')->setOption('model', $model->getPlugin('image'));

        $this->get('images-images')->setOption('model', $model->getPlugin('images'));

        if(!$model->get('depend')) {
            $this->get('depend')->setValue((int) $_GET['parent']);
        }

        $this->get('depend')->setValue(12);
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
            'name' => 'depend',
            'type'  => 'Zend\Form\Element\Hidden',
        ));

        $this->add(array(
            'name' => 'images-images',
            'type'  => 'Aptero\Form\Element\Admin\Images',
            'options' => array(),
        ));

        $this->add(array(
            'name' => 'title',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label'     => 'Заголовок',
            )
        ));

        $this->add(array(
            'name' => 'sort',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label'     => 'Сортировка',
            )
        ));

        $this->add(array(
            'name' => 'video',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label'     => 'Youtube ID',
            )
        ));

        $this->add(array(
            'name' => 'image-image',
            'type'  => 'Aptero\Form\Element\Admin\Image',
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

        $this->add(array(
            'name' => 'type',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'options' => array(
                    1 => 'Заголовок',
                    2 => 'Текст',
                    3 => 'Фото',
                ),
                'label' => 'Шаблон',
            ),
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        /*$inputFilter->add($factory->createInput(array(
            'name'     => 'title',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
        )));*/

        $this->setInputFilter($inputFilter);
    }
}