<?php
namespace TasksAdmin\Form;

use Aptero\Db\Entity\EntityFactory;
use Aptero\Form\Form;

use TasksAdmin\Model\Task;
use UserAdmin\Model\User;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class TasksEditForm extends Form
{
    public function setModel($model)
    {
        parent::setModel($model);

        $users = EntityFactory::collection(new User());
        $users->select()->where(array('type' => User::ROLE_ADMIN));

        $this->get('user_id')->setOptions(array(
            'model'      => $model->getPlugin('user'),
            'collection' => $users,
            'empty'      => '',
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
            'name' => 'user_id',
            'type'  => 'Aptero\Form\Element\TreeSelect',
            'options' => array(
                'label'   => 'Исполнитель'
            ),
        ));

        $this->add(array(
            'name' => 'name',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Название',
            ),
        ));

        $this->add(array(
            'name' => 'desc',
            'type'  => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label' => 'Описание',
            ),
        ));

        $this->add(array(
            'name' => 'status',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'options' => Task::$status,
                'label' => 'Важность',
            ),
        ));
    }

    public function setFilters() {}
}