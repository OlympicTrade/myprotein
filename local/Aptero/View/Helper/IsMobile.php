<?php
namespace Aptero\View\Helper;

use Zend\View\Helper\AbstractHelper;

class IsMobile extends AbstractHelper
{
    public function __invoke($options = array())
    {
        return strpos($_SERVER['HTTP_HOST'], 'm.') === 0;
    }
}