<?php
namespace Aptero\String;

class Numbers
{
    /**
     * example \Aptero\String\Numbers::declension($number, ['яблоко', 'яблока', 'яблок'])
     *
     * @param $number
     * @param $endingArray
     * @return string
     */
    static public function declension($number, $endingArray)
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

        return $ending;
    }
}