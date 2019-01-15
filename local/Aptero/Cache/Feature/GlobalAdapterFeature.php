<?php
namespace Aptero\Cache\Feature;

use Zend\Cache\Storage\Adapter\AbstractAdapter as Adapter;

class GlobalAdapterFeature
{
    /**
     * @var Adapter[]
     */
    protected static $staticAdapters = array();

    /**
     * @param Adapter $adapter
     * @param string $key
     */
    public static function setStaticAdapter(Adapter $adapter, $key = 'data')
    {
        static::$staticAdapters[$key] = $adapter;
    }


    /**
     * @param string $key
     * @return Adapter
     */
    public static function getStaticAdapter($key = 'data')
    {
        if (isset(static::$staticAdapters[$key])) {
            return static::$staticAdapters[$key];
        }
    }
}
