<?php
namespace Aptero\View\Helper;

use Zend\Escaper\Escaper;
use Zend\View\Helper\AbstractHelper;

class Escape extends AbstractHelper
{
    protected $escaper = null;

    public function __construct()
    {
        $this->escaper = new Escaper('utf-8');
    }

    public function __invoke($str)
    {
        return $this->escaper->escapeHtml($str);
    }
}