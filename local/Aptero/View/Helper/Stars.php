<?php
namespace Aptero\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Stars extends AbstractHelper
{
    public function __invoke($stars)
    {
        $html =
            '<div class="stars">';

        for($i = 0; $i <= 4; $i++) {
            $starFilling = $stars - $i;

            if($starFilling >= 0.6) {
                $st = '<i class="fas full fa-star"></i>';
            } elseif ($starFilling >= 0.1) {
                $st = '<i class="fas half fa-star-half"></i>';
            } else {
                $st = '<i class="fas fa-star"></i>';
            }

            $html .= $st;
        }

        $html .=
            '</div>';

        return $html;
    }
}