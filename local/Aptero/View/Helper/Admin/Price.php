<?php
namespace Aptero\View\Helper\Admin;

use Zend\View\Helper\AbstractHelper;

class Price extends AbstractHelper
{
    public function __invoke($price)
    {
        return preg_replace('/(\d)(?=(\d\d\d)+([^\d]|$))/i', '$1 ', $price) . ' руб.';
    }
}