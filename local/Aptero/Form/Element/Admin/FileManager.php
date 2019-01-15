<?php
namespace Aptero\Form\Element\Admin;

use Zend\Form\Element;
use Zend\Form\FormInterface;

class FileManager extends Element
{
    protected $attributes = array(
        'type' => 'filemanager',
    );
}
