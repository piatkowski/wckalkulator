<?php

namespace WCKalkulator;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Class GlobalParameter
 * @package WCKalkulator
 * @author Krzysztof Piątkowski
 * @license GPLv2
 * @since 1.2.0
 */
class GlobalParameter extends GlobalParametersPostType
{
    const CACHE_PREFIX = "wck_global_parameter_";
    
    /**
     * Get parameter value by its name.
     *
     * @param $name
     * @return float|false
     * @since 1.2.0
     */
    public static function get_value($param_name)
    {
        /*
         * parse {global:param_name} string
         */
        if(strlen($param_name) > 0 && $param_name[0] === '{') {
            $param_name = str_replace(array('{global:', '}'), array('',''), $param_name);
        }
        
        /*
         * Read cached value first
         */
        $cached_value = Cache::get(self::CACHE_PREFIX . $param_name);
        if (!is_null($cached_value)) {
            return $cached_value;
        }
        
        /*
         * Search in all posts
         */
        foreach (self::get_all() as $name => $value) {
            if ($name === $param_name) {
                return $value;
            }
        }
        return false;
    }
    
    /**
     * Get all global parameters as an array. Cache values.
     *
     * @return array
     * @since 1.2.0
     */
    public static function get_all()
    {
        $output = array();
        foreach (self::get_posts() as $post) {
            $name = get_post_meta($post->ID, '_wck_param_name', true);
            $value = get_post_meta($post->ID, '_wck_param_value', true);
            $output[$name] = (new ExpressionLanguage())->evaluate($value);
            Cache::store(self::CACHE_PREFIX . $name, $value);
        }
        return $output;
    }
    
    /**
     * @return \WP_Post[]
     */
    private static function get_posts()
    {
        return get_posts(array(
            'post_type' => self::POST_TYPE,
            'numberposts' => -1,
            'per_page' => -1,
            'post_status' => 'publish'
        ));
    }
}