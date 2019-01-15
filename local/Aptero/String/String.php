<?php
namespace Aptero\String;

class String
{
    static public function randomString($lenght = 15)
    {
        return substr(md5(rand()), 0, $lenght);
    }
}