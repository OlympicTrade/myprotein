<?php
namespace Aptero\String;

class StringFn
{
    static public function randomString($lenght = 15)
    {
        return substr(md5(rand()), 0, $lenght);
    }
}