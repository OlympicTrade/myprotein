<?php
namespace Aptero\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Declension extends AbstractHelper
{
    /**  example declension($number, ['яблоко', 'яблока', 'яблок']) */
    public function __invoke($number, $endingArray)
    {
        $number = $number % 100;

        if ($number >= 11 && $number <= 19) {
            $ending = $endingArray[2];
        } else {
            $i = $number % 10;
            switch ($i) {
                case (1): $ending = $endingArray[0]; break;
                case (2):
                case (3):
                case (4): $ending = $endingArray[1]; break;
                default: $ending = $endingArray[2];
            }
        }

        return $number . ' ' . $ending;
    }
}