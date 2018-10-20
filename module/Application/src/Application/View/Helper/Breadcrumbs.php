<?php
namespace Application\View\Helper;

use Zend\I18n\View\Helper\AbstractTranslatorHelper;
use Zend\Json\Json;

class Breadcrumbs extends AbstractTranslatorHelper
{
    public function __invoke($crumbs, $options = array())
    {
        $options = array_merge(array(
            'delimiter' => ' <i class="fas fa-angle-right"></i> ',
            'allLinks'  => false,
            'showLast'  => true,
        ), $options);

        $translator = $this->getTranslator();

        $html =
            '<div class="breadcrumbs">';

        $count = $options['allLinks'] ? count($crumbs) : count($crumbs) - 1;

        for($i = 0; $i < $count; $i++) {
            $html .=
                '<a href="' . $crumbs[$i]['url'] . '">'
                    . $translator->translate($crumbs[$i]['name'])
                .'</a>'
                . ($i + 1 < $count ?  $options['delimiter'] : '');
        }

        if(!$options['allLinks'] && $options['showLast']) {
            $html .= $options['delimiter'] . '<span class="crumb">' .  $crumbs[$i]['name'] . '</span>';
        }

        $html .=
            '</div>';

        //Json LD
        $ldCrumbs = array();
        for($i = 0; $i < count($crumbs) - 1; $i++) {
            $ldCrumbs[] = (object) array(
                '@type'    => 'ListItem',
                'position' => ($i + 1),
                'item' => (object) array(
                    '@id'  => 'http://' . $_SERVER['HTTP_HOST'] . $crumbs[$i]['url'],
                    'name' => $translator->translate($crumbs[$i]['name'])
                )
            );
        }

        $jsonLd = (object) array(
            '@context'     => 'http://schema.org',
            '@type'        => 'BreadcrumbList',
            'itemListElement' => $ldCrumbs
        );

        $html .= '<script type="application/ld+json">' . Json::encode($jsonLd) . '</script>';

        return $html;
    }
}