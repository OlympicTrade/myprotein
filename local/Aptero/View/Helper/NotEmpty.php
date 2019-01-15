<?php
namespace Aptero\View\Helper;

use Zend\View\Helper\AbstractHelper;

class NotEmpty extends AbstractHelper
{
    public function __invoke($string, $value)
    {
        if(!$value) {
            return '';
        }

        return str_replace('?', $value, $string);
    }
}