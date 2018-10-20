<?php
namespace Catalog\View\Helper;

use Zend\View\Helper\AbstractHelper;

class CatalogList extends AbstractHelper
{
    public function __invoke($catalogList, $options = array())
    {
        $options = array_merge(array(
            'class' => ''
        ), $options);

        $html = '<ul>';

        $view = $this->getView();

        foreach($catalogList as $category) {
            $url = '/catalog/' . $category->getUrl() . '/';

            $html .=
                '<li' . ($options['class'] ? 'class="' . $options['class'] . '"' : '') . '>'
                    .'<a href="' . $url . '" data-url="' . $url . '">' . $category->get('name') . '</a>'
                .'</li>';
        }

        $html .=
            '</ul>';

        return $html;
    }
}