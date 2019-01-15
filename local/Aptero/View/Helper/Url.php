<?php
namespace Aptero\View\Helper;

use Zend\View\Helper\Url as ZendUrl;

class Url extends ZendUrl
{
    public function __invoke($name = null, $params = array(), $options = array(), $reuseMatchedParams = false)
    {
        $params = array_merge(
            array(
                'locale' => \Locale::getDefault()
            ),
            $params);

        return parent::_invoke($name, $params, $options, $reuseMatchedParams);
    }
}