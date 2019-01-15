<?php
namespace Aptero\Debug;

class Debug
{
    static $timer = 0;

    public static function dump($obj)
    {
        echo '<pre>';
        var_dump($obj);
        echo '</pre>';
    }

    public static function timerStart()
    {
        self::$timer = microtime(true);
    }

    public static function timerEnd()
    {
        echo '<br>' .  (microtime(true) - self::$timer) . '<br>';
    }
}
