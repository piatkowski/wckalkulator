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

    /** Check whether a key exists, including keys whose value is null. */
    public static function has($key)
    {
        return is_array(self::$cache) && array_key_exists($key, self::$cache);
    }

    /** Remove a cached value. */
    public static function forget($key)
    {
        if (is_array(self::$cache)) {
            unset(self::$cache[$key]);
        }
    }

    /** Remove cached values with a shared prefix. */
    public static function forget_prefix($prefix)
    {
        if (!is_array(self::$cache)) {
            return;
        }
        foreach (array_keys(self::$cache) as $key) {
            if (strpos((string) $key, (string) $prefix) === 0) {
                unset(self::$cache[$key]);
            }
        }
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
