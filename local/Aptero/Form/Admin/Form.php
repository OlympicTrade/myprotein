<?php
namespace Aptero\Form\Admin;

class Form extends \Aptero\Form\Form {
    public function addMeta($prefix = '', $help = '')
    {
        $this->add(array(
            'name' => $prefix . 'title',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Title',
            ),
        ));

        $this->add(array(
            'name' => $prefix . 'description',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Description',
            ),
        ));
    }
}