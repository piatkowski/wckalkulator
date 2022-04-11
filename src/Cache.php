<?php

namespace WCKalkulator;

/**
 * Class Cache
 *
 * Store and get values
 *
 * @package WCKalkulator
 * @author Krzysztof Piątkowski
 * @license GPLv2
 * @since 1.1.0
 */
class Cache
{
    /**
     * @var array
     */
    protected static $cache;
    
    /**
     * Get cached value
     *
     * @param $key
     * @return mixed|null
     * @since 1.1.0
     */
    public static function get($key)
    {
        return isset(self::$cache[$key]) ? self::$cache[$key] : null;
    }
    
    /**
     * Get cached value and remove from array
     *
     * @param $key
     * @return mixed|null
     * @since 1.1.0
     */
    public static function get_once($key)
    {
        $value = self::get($key);
        unset(self::$cache[$key]);
        return $value;
    }
    
    /**
     * Store the value
     *
     * @param $key
     * @param $value
     * @since 1.1.0
     */
    public static function store($key, $value)
    {
        self::$cache[$key] = $value;
    }
}