<?php

namespace Aptero\Cookie;

use Zend\Json\Json;

class Cookie
{
    /**
     * @param string $name
     * @param string $val
     * @param int $time
     * @param string $path
     */
    static public function setCookie($name, $val = '', $time = null, $path = '/')
    {
        if($time === null) {
            $time = time() + (60 * 60 * 24 * 360);
        } else {
            $time = time() + (60 * 60 * 24 * $time);
        }

        if(!is_string($val)) {
            $val = Json::encode($val);
        }

        setcookie($name, $val, $time, $path);
    }

    /**
     * @param string $name
     * @param bool $json
     * @return null
     */
    static public function getCookie($name, $json = false)
    {
        if(!isset($_COOKIE[$name])) {
            return null;
        }

        if(!$json) {
            return $_COOKIE[$name];
        }

        try {
            $jsonData = Json::decode($_COOKIE[$name]);
        } catch (\Exception $e) {
            self::setCookie($name);
            $jsonData = [];
        }

        return $jsonData;
    }
}