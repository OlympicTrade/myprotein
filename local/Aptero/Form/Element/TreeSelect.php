<?php
namespace Aptero\Form\Element;

use Zend\Form\Element;
use Zend\Form\FormInterface;

class TreeSelect extends Element
{
    protected $attributes = array(
        'type' => 'select',
    );
	
	public function setOption($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }
}
