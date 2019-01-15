<?php
namespace Aptero\String;

class Price
{
    static public function priceToStr($number, $fraction = false)
    {
        return preg_replace('/(\d)(?=(\d\d\d)+([^\d]|$))/i', '$1 ', $number);
    }

    static public function nbrToStr($number, $fraction = false)
    {
        return preg_replace('/(\d)(?=(\d\d\d)+([^\d]|$))/i', '$1 ', $number);
    }
}