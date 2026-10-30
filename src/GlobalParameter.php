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

    private static $admin_warnings = array();
    
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
        $cache_key = self::cache_prefix() . $param_name;
        if (Cache::has($cache_key)) {
            return Cache::get($cache_key);
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
    public static function get_all($public_only = false)
    {
        $list_cache_key = self::cache_prefix() . 'all_' . ($public_only ? 'public' : 'all');
        if (Cache::has($list_cache_key)) {
            return Cache::get($list_cache_key);
        }
        $output = array();
        foreach (self::get_posts() as $post) {
            $name = get_post_meta($post->ID, '_wck_param_name', true);
            $expression = get_post_meta($post->ID, '_wck_param_value', true);
            $is_public = get_post_meta($post->ID, '_wck_param_public', true) === '1';
            $is_versioned = (int) get_post_meta($post->ID, '_wck_schema_version', true) > 0;

            if ($is_versioned && !self::is_valid_name($name)) {
                self::queue_admin_warning($post->ID);
                continue;
            }
            if ($is_versioned && array_key_exists($name, $output)) {
                self::queue_admin_warning($post->ID);
                continue;
            }

            $value = null;
            $error = null;
            if (!self::evaluate_expression($expression, $value, $error)) {
                self::queue_admin_warning($post->ID);
                continue;
            }

            // Unversioned 1.6.1 parameters were all exposed to the live preview.
            if ($public_only && !$is_public && $is_versioned) {
                continue;
            }

            $output[$name] = $value;
            Cache::store(self::cache_prefix() . $name, $value);
        }
        Cache::store($list_cache_key, $output);
        return $output;
    }

    /** Clear request-level parameter values after an edit. */
    public static function clear_cache()
    {
        Cache::forget_prefix(self::cache_prefix());
    }

    /** Keep request-local parameter values isolated across multisite blog switches. */
    private static function cache_prefix()
    {
        $blog_id = function_exists('get_current_blog_id') ? (int) get_current_blog_id() : 0;
        return self::CACHE_PREFIX . $blog_id . '_';
    }

    /** Validate the parameter name used by formulas. */
    public static function is_valid_name($name)
    {
        return is_string($name) && preg_match('/\\A[a-zA-Z0-9_]+\\z/', $name) === 1;
    }

    /**
     * Parse and evaluate a parameter expression, allowing only finite numeric values
     * and nested arrays of finite numeric values.
     *
     * @param mixed $expression
     * @param mixed $value Result value, when valid.
     * @param string|null $error Generic validation reason, never the raw exception.
     * @return bool
     */
    public static function evaluate_expression($expression, &$value = null, &$error = null)
    {
        $value = null;
        $error = null;
        if (!is_string($expression) || trim($expression) === '' || strlen($expression) > 512) {
            $error = 'invalid_expression';
            return false;
        }

        try {
            $language = new ExpressionLanguage();
            $parsed = $language->parse($expression, array());
            $result = $language->evaluate($parsed, array());
        } catch (\Throwable $exception) {
            $error = 'invalid_expression';
            return false;
        }

        if (!self::is_allowed_value($result, 0)) {
            $error = 'invalid_type';
            return false;
        }

        $value = $result;
        return true;
    }

    /** Check numeric expression results recursively with bounded size and depth. */
    private static function is_allowed_value($value, $depth)
    {
        if (is_int($value)) {
            return true;
        }
        if (is_float($value)) {
            return is_finite($value);
        }
        if (!is_array($value) || !$value || $depth >= 10 || count($value) > 500) {
            return false;
        }
        foreach ($value as $item) {
            if (!self::is_allowed_value($item, $depth + 1)) {
                return false;
            }
        }
        return true;
    }

    private static function queue_admin_warning($post_id)
    {
        if (function_exists('is_admin') && is_admin() && !isset(self::$admin_warnings[$post_id])) {
            self::$admin_warnings[$post_id] = true;
            add_action('admin_notices', array(__CLASS__, 'display_admin_warnings'));
        }
    }

    public static function display_admin_warnings()
    {
        foreach (array_keys(self::$admin_warnings) as $post_id) {
            $link = get_edit_post_link($post_id);
            $name = get_post_meta($post_id, '_wck_param_name', true);
            $label = self::is_valid_name($name) ? $name : (string) $post_id;
            echo '<div class="notice notice-error"><p>';
            printf(
                esc_html__('Parametr globalny „%1$s” jest niepoprawny lub ma zduplikowaną nazwę i został pominięty. Popraw go przed publikacją.', 'wc-kalkulator'),
                esc_html($label)
            );
            if ($link) {
                echo ' <a href="' . esc_url($link) . '">' . esc_html__('Edytuj parametr', 'wc-kalkulator') . '</a>';
            }
            echo '</p></div>';
        }
        self::$admin_warnings = array();
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
